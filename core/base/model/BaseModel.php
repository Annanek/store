<?php

namespace core\base\model;

use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel extends BaseModelMethods
{

    use Singleton;

    protected $db;

    private function __construct()
    {
        $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);

        if ($this->db->connect_error) {

            throw new DbException('Ошибка подключения к базе данных: '
                . $this->db->connect_errno . ' ' . $this->db->connect_error);
        }

        $this->db->query("SET NAMES UTF8");
    }

    /**
     * @param $query
     * @param string $crud = r - SELECT / c - INSERT / u - UPDATE / d - DELETE
     * @param $return_id
     * @return array|bool
     * @throws DbException
     */

    final public function query($query, $crud = 'r', $return_id = false) {

        $result = $this->db->query($query);

        if ($this->db->affected_rows === -1) {
            throw new DbException('Ошибка в SQL запросе: '
                . $query . ' - ' . $this->db->errno . ' ' . $this->db->error
            );
        }

        switch($crud) {

            case 'r':

                if($result->num_rows) {
                    $res = [];

                    for($i = 0; $i < $result->num_rows; $i++) {
                        $res[] = $result->fetch_assoc();
                    }

                    return $res;
                }

                return false;

                break;

            case 'c':

                if($return_id) return $this->db->insert_id;

                return true;

                break;

            default:

                return true;

                break;

        }

    }

    /**
     * @param $table
     * @param $set
     * 'fields' => ['id', 'name']
     * 'where' => ['fio' => 'smirnova', 'name' => 'Masha', 'surname' => 'Sergeevna']
     * 'operand' => ['=', '<>']
     * 'condition' => ['AND']
     * 'order' => ['fio', 'name']
     * 'order_direction' => ['ASC', 'DESC']
     * 'limit' => '1'
     * 'join' => [
     *      [
     *          'table' => 'join_table1',
     *          'fields' => ['id as j_id', 'name as j_name'],
     *          'type' => 'left',
     *          'where' => ['name' => 'sasha'],
     *          'operand' => ['='],
     *          'condition' => ['OR'],
     *          'on' => ['id', 'parent_id'],
     *          'group_condition' => 'AND'
     *      ],
     *      'join_table1' => [
     *          'table' => 'join_table2',
     *          'fields' => ['id as j2_id', 'name as j2_name'],
     *          'type' => 'left',
     *          'where' => ['name' => 'sasha'],
     *          'operand' => ['<>'],
     *          'condition' => ['AND'],
     *          'on' => [
     *              'table' => 'teachers',
     *              'fields' => ['id', 'parent_id']
     *          ]
     *      ]
     *
     */

    final public function get($table, $set = []) {

        $fields = $this->createFields($set, $table);

        $order = $this->createOrder($set, $table);

        $where = $this->createWhere($set, $table);

        if(!$where) $new_where = true;
            else $new_where = false;

        $join_arr = $this->createJoin($set, $table, $new_where);

        $fields .= $join_arr['fields'];
        $join = $join_arr['join'];
        $where .= $join_arr['where'];

        $fields = rtrim($fields, ',');

        $limit = $set['limit'] ? 'LIMIT ' . $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);

    }

    /**
     * @param $table - таблица для вставки данных
     * @param array $set - массив параметров:
     * fields => [поле => значение]; если не указан, то обрабатывается $_POST[поле => значение]
     * разрешена передача NOW() в качестве Mysql функции обычной строкой
     * files => [поле => значение]; можно подать массив вида [поле => [масств значений]]
     * except => ['исключение1', 'исключение2'] - исключает данные элементы массива из добавления в запрос
     * return_id => true|false - возвращать или нет идентификатор вставленной записи
     * @return mixed
     */

    final public function add($table, array $set) {
        //todo: l24 ~ 10
        $set['fields'] = (!empty($set['fields']) && is_array($set['fields'])) ? $set['fields'] : false;
        $set['files'] = (!empty($set['files']) && is_array($set['files'])) ? $set['files'] : false;
        $set['return_id'] = !empty($set['return_id']) ? true : false;
        $set['except'] = (!empty($set['except']) && is_array($set['except'])) ? $set['except'] : false;

        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);

        if($insert_arr) {
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES ({$insert_arr['values']})";

            return $this->query($query, 'c', $set['return_id']);
        }

        return false;

    }

}