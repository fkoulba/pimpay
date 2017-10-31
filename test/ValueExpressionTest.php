<?php

require_once __DIR__ . '/../lib/Expression.php';

class ValueExpressionTest extends PHPUnit_Framework_TestCase {
  public function testConstruct() {
    $expr = new ValueExpression(10, 'RUB');
    $this->assertInstanceOf(ValueExpression::class, $expr);
    $this->assertEquals(10, $expr->getAmount());
    $this->assertEquals('RUB', $expr->getCode());
  }

  public function testDescribe() {
    $expr = new ValueExpression(10, 'RUB');
    $this->assertEquals('10RUB', $expr->describe());
  }

  public function testCollapse() {
    $expr = new ValueExpression(10, 'RUB');
    $this->assertEquals(['RUB' => 10], $expr->collapse());
  }

  public function testAsFloat() {
    $expr = new ValueExpression(5, 'USD');
    $this->assertEquals(300, $expr->asFloat(['RUB' => 1, 'USD' => 60]));
  }
}