import sqlite3

db = sqlite3.connect("main.db")

cur = db.cursor()

# Ativando modo WAL (Write-Ahead Logging) para melhorar o desempenho do SQLite.
cur.execute("PRAGMA journal_mode=WAL")

cur.executescript(
    """
    CREATE TABLE customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        access_key TEXT NOT NULL UNIQUE,
        telegram_id INTEGER UNIQUE,
        coins INTEGER DEFAULT 0,
        is_blocked INTEGER DEFAULT 0,
        is_unlimited INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE customers_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        account_email TEXT NOT NULL UNIQUE,
        account_password TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE INDEX idx_account_password ON customers_data(account_password);
    """
)

db.commit()
db.close()
