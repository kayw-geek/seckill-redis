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

    public function redisInit()
    {
        $goodsNum = Goods::where('id',1)->value('num');//库存
        for ($i=0;$i<$goodsNum;$i++){
            Redis::lpush('goodsnum',1);
        }
        $redisNum = Redis::llen('goodsnum');
        echo $redisNum;
    }

    public function buildOrder()
    {
        $goodsPrice = Goods::where('id',1)->value('price');//单价
        $order_num = $this->buildOrderNo();
        $bool = Redis::lpop('goodsnum');
        if(!$bool) return '抢购失败';
        //赋值
        $data['order_num'] = $order_num;
        $data['user_id'] = 1;
        $data['goods_id'] = 1;
        $data['order_price'] = $goodsPrice;
        $res = Order::insert($data);//插入订单数据
        if(!$res) return '抢购失败';
        $numBool = Goods::where('id',1)->decrement('num');//库存自减
    }

}
