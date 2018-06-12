<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function buildOrder()
    {
        
    }

}
