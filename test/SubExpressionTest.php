<?php

require_once __DIR__ . '/../lib/Expression.php';

class SubExpressionTest extends PHPUnit_Framework_TestCase {
  public function testConstruct() {
    $subtrahend = new ValueExpression(10, 'RUB');
    $minuend = new ValueExpression(5, 'USD');

    $expr = new SubExpression($subtrahend, $minuend);
    $this->assertInstanceOf(SubExpression::class, $expr);
    $this->assertEquals($subtrahend, $expr->getSubtrahend());
    $this->assertEquals($minuend, $expr->getMinuend());
  }

  public function testDescribe() {
    $expr = new SubExpression(new ValueExpression(10, 'RUB'), new ValueExpression(5, 'USD'));
    $this->assertEquals('10RUB - 5USD', $expr->describe());
  }

  public function testCollapse() {
    $expr = new SubExpression(new ValueExpression(10, 'RUB'), new ValueExpression(5, 'USD'));
    $this->assertEquals(['RUB' => 10, 'USD' => -5], $expr->collapse());
  }

  public function testAsFloat() {
    $expr = new SubExpression(new ValueExpression(10, 'RUB'), new ValueExpression(5, 'USD'));
    $this->assertEquals(-290, $expr->asFloat(['RUB' => 1, 'USD' => 60]));
  }
}