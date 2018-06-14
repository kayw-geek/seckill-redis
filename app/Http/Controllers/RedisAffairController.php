<?php

namespace App\Http\Controllers;

use App\Goods;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * Class RedisAffairController
 * @package App\Http\Controllers
 * @name:redis队列 秒杀场景测试
 * @author: weikai
 * @date: 2018/6/12 16:16
 */
class RedisAffairController extends Controller
{
    /**
     * @param $goods
     * @param $userId
     * @return bool|string
     * @name: 生成订单号
     * @author: weikai
     * @date: 2018/6/12 14:54
     */
    public function buildOrderNo()
    {

        return date('ymd').rand(1000,9999);
    }

    /**
     * @name:redis初始化 库存数量写进redis队列
     * @author: weikai
     * @date: 2018/6/14 15:12
     */
    public function redisInit()
    {
        $goodsNum = Goods::where('id',1)->value('num');//库存
        for ($i=0;$i<$goodsNum;$i++){
            Redis::lpush('goodsnum',1);//有100个库存就写将队列长度添加到100
        }
        $redisNum = Redis::llen('goodsnum');
        echo $redisNum;
    }

    /**
     * @name:redis重置队列 和字符串的值
     * @author: weikai
     * @date: 2018/6/14 15:13
     */
    public function redisDel(){
        Redis::lrem('goodsnum',0,1);//删除队列中所有值为1的
        $userinfos = Redis::keys('userinfo:*');//匹配查询所有redis key
        foreach ($userinfos as $v){
           Redis::del($v);//删除对应key的键值对
        }
       echo  Redis::llen('goodsnum');
        $count =  Redis::keys('userinfo:*');
        print_r($count);
    }

    /**
     * @name:抢购期间下单执行此方法 防止高并发mysql数据异常
     * @author: weikai
     * @date: 2018/6/14 15:13
     */
    public function buildList()
    {
        $goodsPrice = Goods::where('id',1)->value('price');//单价
        $order_num = $this->buildOrderNo();//单号
        $bool = Redis::lpop('goodsnum');//有一个抢购请求就从队列中出一个
        if($bool){
            //赋值
            $data['order_num'] = $order_num;
            $data['user_id'] = rand('1','1000');
            $data['goods_id'] = 1;
            $data['order_price'] = $goodsPrice;
            $json  = json_encode($data);
            Redis::set("userinfo:".$data['user_id'],$json);//将用户下单信息先存进redis
        }

    }

    /**
     * @name:抢购期间由计划任务每秒执行一次（每秒坚持队列中是否有新用户抢购成功，写入订单表，方便用户抢购后查询）
     * @author: weikai
     * @date: 2018/6/14 15:15
     */
    public function buildOrder()
    {
        $userinfos = Redis::keys('userinfo:*');//取出所有的用户下单信息
//        dd($userinfos);
       foreach ($userinfos as $v){
           $orderDatas[] = json_decode(Redis::get($v));
       }
//       dd($orderDatas);
        foreach ($orderDatas as $v){
            $data['user_id'] = $v->user_id;
            $data['goods_id'] = $v->goods_id;
            $data['order_num'] = $v->order_num;
            $data['order_price'] = $v->order_price;
           $res = Order::insert($data);//将储存在redis中的下单信息转存到mysql
           if ($res)
               Goods::where('id',1)->decrement('num');//库存自减
            Redis::del('userinfo:'.$v->user_id);//移除redis中储存的下单信息
        }
    }

}
