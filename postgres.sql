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
  customer_id BIGINT NOT NULL, -- Клиент, FK на tbl_customer(id)
  type VARCHAR NOT NULL, -- Тип транзакции
  amount NUMERIC(10,2) NOT NULL -- Сумма
);

INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (3435, 'loan', 1000.50);
INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (3435, 'interest', 1.50);
INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (3435, 'interest_repayment', 1.50);
INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (4456, 'loan', 7800.00);
INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (4456, 'loan_repayment', 5200.30);
INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (6718, 'loan', 4200.00);
INSERT INTO tbl_loan_transaction (customer_id, type, amount) VALUES (6718, 'loan_repayment', 4200.00);

SELECT DISTINCT
  c.tin,
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