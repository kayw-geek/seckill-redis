<?php

namespace App\Http\Controllers;

use App\Goods;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

/**
 * Class MysqlAffairController
 * @package App\Http\Controllers
 * @name: mysql事务解决并发秒杀场景
 * @author: weikai
 * @date: 2018/6/12 14:39
 */
class MysqlAffairController extends Controller
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
     * @name: 生成订单事务处理
     * @author: weikai
     * @date: 2018/6/12 15:38
     */
    public function buildOrder()
    {
        try{
            DB::transaction(function (){
                $goodsNum = Goods::where('id',1)->value('num');//库存
                $goodsPrice = Goods::where('id',1)->value('price');//单价
                if($goodsNum<1) return false;
                $orderNo = $this->buildOrderNo();//单号
                $data['order_num'] = $orderNo;
                $data['user_id'] = 1;
                $data['goods_id'] = 1;
                $data['order_price'] = $goodsPrice;
                $bool = Order::insert($data);
                if (!$bool) return false;
                $numBool = Goods::where('id',1)->decrement('num');//库存自减
                if(!$numBool) return false;
                echo '添加成功';
            });

        }catch (Exception $e){
            DB::rollBack();
            exit('error');
        }

        DB::commit();

    }
}
