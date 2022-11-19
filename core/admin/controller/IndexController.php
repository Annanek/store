<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{

    protected function inputData() {

        $db = Model::instance();

        $table = 'teachers';

        $color = ['red', 'blue', 'black'];

        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => "O'Raily"],
            'limit' => '1'
        ])[0];
            /*'operand' => ['IN', '<>'],*/
            /*'condition' => ['AND','OR'],*/
//            'order' => ['name'],
//            'order_direction' => ['DESC'],
//            'limit' => '1',
//            'join' => [
//                 [
//                    'table' => 'join_table1',
//                    'fields' => ['id as j_id', 'name as j_name'],
//                    'type' => 'left',
//                    'where' => ['name' => 'sasha'],
//                    'operand' => ['='],
//                    'condition' => ['OR'],
//                    'on' => [
//                        'table' => 'teachers',
//                        'fields' => ['id', 'parent_id']
//                    ]
//                ],
//                'join_table2' => [
//                    'table' => 'join_table2',
//                    'fields' => ['id as j2_id', 'name as j2_name'],
//                    'type' => 'left',
//                    'where' => ['name' => 'sasha'],
//                    'operand' => ['<>'],
//                    'condition' => ['AND'],
//                    'on' => ['id', 'parent_id']
//                ]
//            ]
 //       ]);

        exit('id =' . $res['id'] . ' Name = ' . $res['name']);
    }

}