<?php

require_once __DIR__ . '/lib/Expression.php';

function RUB($value) {
  $factory = new ExpressionFactory();
  return $factory->buildExpression('val', [$value, 'RUB']);
}

function USD($value) {
  $factory = new ExpressionFactory();
  return $factory->buildExpression('val', [$value, 'USD']);
}

$expr = (RUB(10)->mul(5)->add(USD(5))->sub(RUB(3)))->mul(2);

echo $expr->describe() . "\n";

print_r($expr->collapse());

echo $expr->asFloat(['RUB' => 1, 'USD' => 63.23]) . "\n";
