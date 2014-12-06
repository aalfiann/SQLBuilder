<?php
namespace SQLBuilder\Expression;
use SQLBuilder\Expression\Expr;
use SQLBuilder\Expression\ListExpr;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ToSqlInterface;
use LogicException;

class IsExpr extends Expr implements ToSqlInterface { 

    public $exprStr;

    public $boolean;

    public function __construct($exprStr, $boolean)
    {
        $this->exprStr = $exprStr;

        // Validate boolean type
        if (!is_bool($boolean) && !is_null($boolean) && ! $boolean instanceof Unknown) {
            throw new LogicException('Invalid boolean type');
        }

        $this->boolean = $boolean;
    }

    public function toSql(BaseDriver $driver) {
        return $this->exprStr . ' IS ' . $driver->deflate($this->boolean);
    }
}
