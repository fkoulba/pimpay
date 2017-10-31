<?php

require_once __DIR__ . '/../lib/Expression.php';

class DivExpressionTest extends PHPUnit_Framework_TestCase {
  public function testConstruct() {
    $term1 = new ValueExpression(10, 'USD');
    $term2 = 5;

    $expr = new DivExpression($term1, $term2);
    $this->assertInstanceOf(DivExpression::class, $expr);
    $this->assertEquals($term1, $expr->getDividend());
    $this->assertEquals($term2, $expr->getDivisor());
  }

  public function testDescribe() {
    $expr = new DivExpression(new ValueExpression(10, 'USD'), 5);
    $this->assertEquals('(10USD) / 5', $expr->describe());
  }

  public function testCollapse() {
    $expr = new DivExpression(new ValueExpression(10, 'USD'), 5);
    $this->assertEquals(['USD' => 2], $expr->collapse());
  }

  public function testAsFloat() {
    $expr = new DivExpression(new ValueExpression(10, 'USD'), 5);
    $this->assertEquals(120, $expr->asFloat(['RUB' => 1, 'USD' => 60]));
  }
}