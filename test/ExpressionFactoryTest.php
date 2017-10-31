<?php

require_once __DIR__ . '/../lib/Expression.php';

class ExpressionFactoryTest extends PHPUnit_Framework_TestCase {
  public function testBuildExpression() {
    $factory = new ExpressionFactory();

    $expr = $factory->buildExpression('val', [10, 'RUB']);
    $this->assertInstanceOf(ValueExpression::class, $expr);
    $this->assertEquals(10, $expr->getAmount());
    $this->assertEquals('RUB', $expr->getCode());

    $term1 = new ValueExpression(5, 'USD');
    $term2 = 10;
    $expr = $factory->buildExpression('mul', [$term1, $term2]);
    $this->assertInstanceOf(MulExpression::class, $expr);
    $this->assertEquals($term1, $expr->getTerm1());
    $this->assertEquals($term2, $expr->getTerm2());

    try {
      $expr = $factory->buildExpression('foo', [1, 2]);
      $this->fail('Должно выкинуть исключение');
    } catch (Exception $e) {
      $this->assertEquals('Неизвестный тип выражения "foo"!', $e->getMessage());
    }
  }
}

