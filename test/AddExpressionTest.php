<?php

require_once __DIR__ . '/../lib/Expression.php';

class AddExpressionTest extends PHPUnit_Framework_TestCase {
  public function testConstruct() {
    $term1 = new ValueExpression(10, 'RUB');
    $term2 = new ValueExpression(5, 'USD');

    $expr = new AddExpression($term1, $term2);
    $this->assertInstanceOf(AddExpression::class, $expr);
    $this->assertEquals($term1, $expr->getTerm1());
    $this->assertEquals($term2, $expr->getTerm2());
  }

  public function testDescribe() {
    $expr = new AddExpression(new ValueExpression(10, 'RUB'), new ValueExpression(5, 'USD'));
    $this->assertEquals('10RUB + 5USD', $expr->describe());
  }

  public function testCollapse() {
    $expr = new AddExpression(new ValueExpression(10, 'RUB'), new ValueExpression(5, 'USD'));
    $this->assertEquals(['RUB' => 10, 'USD' => 5], $expr->collapse());
  }

  public function testAsFloat() {
    $expr = new AddExpression(new ValueExpression(10, 'RUB'), new ValueExpression(5, 'USD'));
    $this->assertEquals(310, $expr->asFloat(['RUB' => 1, 'USD' => 60]));
  }
}