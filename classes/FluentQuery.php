<?php

class FluentQuery {
    const INNER_JOIN = 0;
    const LEFT_JOIN = 1;
    const RIGHT_JOIN = 2;

    private $pdo;

    private $select;
    private $from;
    private $where;
    private $order;
    private $limit;
    private $offset;
    private $as;

    private $parameters;

    private $join;
    private $joinType;
    private $joinConnectors;

    private $finalQuery;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->parameters = [];
    }

    public function select($fields) {
        $this->select = $fields;

        return $this;
    }

    public function from($table) {
        $this->from = $table;

        return $this;
    }

    public function where($where) {
        $this->where = $where;

        return $this;
    }

    public function join($type, $relation, $leftConnector, $rightConnector) {
        $this->joinType = $type;
        $this->join = $relation;
        $this->joinConnectors = [$leftConnector, $rightConnector];

        return $this;
    }

    public function innerJoin($relation, $leftConnector, $rightConnector) {
        $this->join(self::INNER_JOIN, $relation, $leftConnector, $rightConnector);

        return $this;
    }

    public function leftJoin($relation, $leftConnector, $rightConnector) {
        $this->join(self::LEFT_JOIN, $relation, $leftConnector, $rightConnector);

        return $this;
    }

    public function rightJoin($relation, $leftConnector, $rightConnector) {
        $this->join(self::RIGHT_JOIN, $relation, $leftConnector, $rightConnector);

        return $this;
    }

    public function presentAs($as) {
        $this->as = $as;

        return $this;
    }

    public function createQuery() {
        $this->finalQuery = "SELECT ";

        $this->finalQuery .= $this->getSelect();
        
        $this->finalQuery .= " FROM " . $this->from;

        if ($this->join != null) {
            $this->finalQuery .= " " . $this->getJoin();
        }

        if (!empty($this->where)) {
            $this->finalQuery .= " WHERE " . $this->where;
        }

        if ($this->isAsSet()) {
            $this->finalQuery = "(" . $this->finalQuery . ") AS " . $this->as;
        }

        return $this->finalQuery;
    }

    public function getSelect() {
        $result = "";

        if ($this->select == "*") {
            $result .= "*";
        } else {
            $i = 0;

            foreach ($this->select as $value) { // jesli jest podzapytanie, to cos zrobic
                if ($i != 0) {
                    $result .= ", ";
                }

                if ($value instanceof FluentQuery) {
                    if (!$value->isAsSet()) {
                        throw new RelationIdentifierNotSetException();
                    }

                    $result .= $value->createQuery();
                } else if (is_array($value)) {
                    $result .= $value[0] . " AS " . $value[1];
                } else {
                    $result .= $value;
                }

                $i++;
            }
        }

        return $result;
    }

    public function getJoin() {
        $join = "";

        if ($this->joinType == self::LEFT_JOIN) {
            $join .= "LEFT JOIN";
        } else if ($this->joinType == self::RIGHT_JOIN) {
            $join .= "RIGHT JOIN";
        } else {
            $join .= "INNER JOIN";
        }

        $join .= " " . $this->join;
        $join .= " ON ";
        $join .= $this->joinConnectors[0] . " = " . $this->joinConnectors[1];

        return $join;
    }

    public function isAsSet() {
        return (!empty($this->as));
    }

    public function query($query) {

    }
}

?>