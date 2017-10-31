CREATE TABLE tbl_customer
(
  id BIGINT NOT NULL, -- Клиент
  tin VARCHAR NOT NULL UNIQUE -- Уникальный ИНН клиента
);

INSERT INTO tbl_customer (id, tin) VALUES (3435, 23581783732);
INSERT INTO tbl_customer (id, tin) VALUES (4456, 59380919099);
INSERT INTO tbl_customer (id, tin) VALUES (6718, 37640194641);

CREATE TABLE tbl_loan_transaction
(
  id BIGINT NOT NULL, -- PK транзакции, нужен в треш-варианте
  customer_id BIGINT NOT NULL, -- Клиент, FK на tbl_customer(id)
  type VARCHAR NOT NULL, -- Тип транзакции
  amount NUMERIC(10,2) NOT NULL -- Сумма
);

INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (1, 3435, 'loan', 1000.50);
INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (2, 3435, 'interest', 1.50);
INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (3, 3435, 'interest_repayment', 1.50);
INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (4, 4456, 'loan', 7800.00);
INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (5, 4456, 'loan_repayment', 5200.30);
INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (6, 6718, 'loan', 4200.00);
INSERT INTO tbl_loan_transaction (id, customer_id, type, amount) VALUES (7, 6718, 'loan_repayment', 4200.00);

-- Вариант с GROUP BY
-- Нет возможности выдать долю от общего кредитного портфеля, т.к. оконные функции нельзя использовать одновременно с GROUP BY
SELECT
  c.tin AS customer_tin,
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) AS customer_portfolio
FROM
  tbl_customer AS c
  INNER JOIN tbl_loan_transaction AS t ON t.customer_id = c.id
GROUP BY
  c.tin
HAVING
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) != 0
ORDER BY
  customer_portfolio DESC;

-- Вариант с оконными функциями
-- Нет возможности отфильтровать по нулевым портфелям, т.к. оконные функции выполняются после WHERE и HAVING
SELECT DISTINCT
  c.tin AS customer_tin,
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) OVER (PARTITION BY t.customer_id) AS customer_portfolio,
  ROUND(
    (
      SUM(
        (
          CASE
            WHEN t.type IN ('loan', 'interest') THEN 1
            WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
            ELSE 0
          END
        ) * t.amount
      ) OVER (PARTITION BY t.customer_id)
    ) / (
      SUM(
        (
          CASE
            WHEN t.type IN ('loan', 'interest') THEN 1
            WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
            ELSE 0
          END
        ) * t.amount
      ) OVER ()
    ) * 100,
    2
  ) AS percent_of_total_portfolio
FROM
  tbl_customer AS c
  INNER JOIN tbl_loan_transaction AS t ON t.customer_id = c.id
ORDER BY
  customer_portfolio DESC;

-- Вариант с подзапросом с оконными функциями
SELECT
  customer_tin,
  customer_portfolio,
  percent_of_total_portfolio
FROM
  (
    SELECT DISTINCT
      c.tin AS customer_tin,
      SUM(
        (
          CASE
            WHEN t.type IN ('loan', 'interest') THEN 1
            WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
            ELSE 0
          END
        ) * t.amount
      ) OVER (PARTITION BY t.customer_id) AS customer_portfolio,
      ROUND(
        (
          SUM(
            (
              CASE
                WHEN t.type IN ('loan', 'interest') THEN 1
                WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
                ELSE 0
              END
            ) * t.amount
          ) OVER (PARTITION BY t.customer_id)
        ) / (
          SUM(
            (
              CASE
                WHEN t.type IN ('loan', 'interest') THEN 1
                WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
                ELSE 0
              END
            ) * t.amount
          ) OVER ()
        ) * 100,
        2
      ) AS percent_of_total_portfolio
    FROM
      tbl_customer AS c
      INNER JOIN tbl_loan_transaction AS t ON t.customer_id = c.id
  ) AS customer_portfolio_query
WHERE
  customer_portfolio != 0
ORDER BY
  customer_portfolio DESC;

-- Треш-вариант с GROUP BY и CROSS JOIN
-- В комментариях, наверное, не нуждается, это треш. Но считает верно =)
SELECT
  c.tin AS customer_tin,
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) / COUNT(DISTINCT t_all.id) AS customer_portfolio,
  ROUND(
    (
      SUM(
        (
          CASE
            WHEN t.type IN ('loan', 'interest') THEN 1
            WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
            ELSE 0
          END
        ) * t.amount
      ) / COUNT(DISTINCT t_all.id)
    ) / (
      SUM(
        (
          CASE
            WHEN t_all.type IN ('loan', 'interest') THEN 1
            WHEN t_all.type IN ('loan_repayment', 'interest_repayment') THEN -1
            ELSE 0
          END
        ) * t_all.amount
      ) / COUNT(DISTINCT t.id)
    ) * 100,
    2
  ) AS percent_of_total_portfolio
FROM
  tbl_customer AS c
  INNER JOIN tbl_loan_transaction AS t ON t.customer_id = c.id
  CROSS JOIN tbl_loan_transaction AS t_all
GROUP BY
  c.tin
HAVING
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) != 0
ORDER BY
  customer_portfolio DESC;


-- Вариант с GROUP BY и оконными функциями
SELECT
  c.tin AS customer_tin,
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) AS customer_portfolio,
  ROUND(
    SUM(
      (
        CASE
          WHEN t.type IN ('loan', 'interest') THEN 1
          WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
          ELSE 0
        END
      ) * t.amount
    ) / SUM(
      SUM(
        (
          CASE
            WHEN t.type IN ('loan', 'interest') THEN 1
            WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
            ELSE 0
          END
        ) * t.amount
      )
    ) OVER () * 100,
    2
  ) AS percent_of_total_portfolio
FROM
  tbl_customer AS c
  INNER JOIN tbl_loan_transaction AS t ON t.customer_id = c.id
GROUP BY
  c.tin
HAVING
  SUM(
    (
      CASE
        WHEN t.type IN ('loan', 'interest') THEN 1
        WHEN t.type IN ('loan_repayment', 'interest_repayment') THEN -1
        ELSE 0
      END
    ) * t.amount
  ) != 0
ORDER BY
  customer_portfolio DESC;