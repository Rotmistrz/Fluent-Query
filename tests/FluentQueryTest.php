<?php

use PHPUnit\Framework\TestCase;

class FluentQueryTest extends TestCase {
    private $fq;
    private $innerFq;

    public function setUp() {
        $this->fq = new FluentQuery(DataBase::getInstance());
        $this->innerFq = new FluentQuery(DataBase::getInstance());
    }

    public function testGetSelect() {
        $expectedResult = "lorem, ipsum, and_another";

        $this->fq->select(["lorem", "ipsum", "and_another"]);

        $result = $this->fq->getSelect();

        $this->assertEquals($expectedResult, $result);

        $expectedResult = "lorem, ipsum, (SELECT COUNT(*) FROM another_table) AS amount";

        $this->innerFq->select(["COUNT(*)"])->from("another_table")->presentAs("amount");

        $this->fq->select(["lorem", "ipsum", $this->innerFq]);

        $result = $this->fq->getSelect();

        $this->assertEquals($expectedResult, $result);

        $expectedResult = "lorem, ipsum, (SELECT COUNT(*) FROM another_table) AS amount";

        $this->innerFq = new FluentQuery(DataBase::getInstance());
        $this->innerFq->select(["COUNT(*)"])->from("another_table");

        $this->fq->select(["lorem", "ipsum", $this->innerFq]);

        $this->expectException(RelationIdentifierNotSetException::class);

        $result = $this->fq->getSelect();
    }

    public function testGetJoin() {
        $this->fq->innerJoin("exemplary_table", "exemplary_table.id", "another_table.id");

        $expectedResult = "INNER JOIN exemplary_table ON exemplary_table.id = another_table.id";
        $result = $this->fq->getJoin();

        $this->assertEquals($expectedResult, $result);


        $this->fq->leftJoin("exemplary_table", "exemplary_table.id", "another_table.id");

        $expectedResult = "LEFT JOIN exemplary_table ON exemplary_table.id = another_table.id";
        $result = $this->fq->getJoin();

        $this->assertEquals($expectedResult, $result);


        $this->fq->rightJoin("exemplary", "exemplary.id", "another_table.id");

        $expectedResult = "RIGHT JOIN exemplary ON exemplary.id = another_table.id";
        $result = $this->fq->getJoin();

        $this->assertEquals($expectedResult, $result);
    }
}

?>