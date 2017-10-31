<?php

require_once __DIR__ . '/../lib/Expression.php';

class MulExpressionTest extends PHPUnit_Framework_TestCase {
  public function testConstruct() {
    $term1 = new ValueExpression(5, 'USD');
    $term2 = 10;

    $expr = new MulExpression($term1, $term2);
    $this->assertInstanceOf(MulExpression::class, $expr);
    $this->assertEquals($term1, $expr->getTerm1());
    $this->assertEquals($term2, $expr->getTerm2());
  }

  public function testDescribe() {
    $expr = new MulExpression(new ValueExpression(5, 'USD'), 10);
    $this->assertEquals('(5USD) * 10', $expr->describe());
  }

  public function testCollapse() {
    $expr = new MulExpression(new ValueExpression(5, 'USD'), 10);
    $this->assertEquals(['USD' => 50], $expr->collapse());
  }

  public function testAsFloat() {
    $expr = new MulExpression(new ValueExpression(5, 'USD'), 10);
    $this->assertEquals(3000, $expr->asFloat(['RUB' => 1, 'USD' => 60]));
  }
}