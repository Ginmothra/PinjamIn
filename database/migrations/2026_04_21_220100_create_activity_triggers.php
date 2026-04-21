<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Trigger untuk Tabel ITEMS (Stok/Baru)
        DB::unprepared('
            CREATE TRIGGER after_items_insert AFTER INSERT ON items
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, new_data)
                VALUES ("ITEMS", "INSERT", "items", NEW.id, JSON_OBJECT("name", NEW.name, "stock", NEW.stock));
            END
        ');

        DB::unprepared('
            CREATE TRIGGER after_items_update AFTER UPDATE ON items
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, old_data, new_data)
                VALUES ("ITEMS", "UPDATE", "items", NEW.id, 
                    JSON_OBJECT("stock", OLD.stock), 
                    JSON_OBJECT("stock", NEW.stock));
            END
        ');

        // 2. Trigger untuk Tabel LOANS (Peminjaman)
        DB::unprepared('
            CREATE TRIGGER after_loans_insert AFTER INSERT ON loans
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, new_data)
                VALUES ("LOANS", "INSERT", "loans", NEW.id, JSON_OBJECT("code", NEW.loan_code, "user_id", NEW.user_id));
            END
        ');

        DB::unprepared('
            CREATE TRIGGER after_loans_update AFTER UPDATE ON loans
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO activity_logs (log_type, action, table_name, record_id, old_data, new_data)
                    VALUES ("LOANS", "STATUS_CHANGE", "loans", NEW.id, 
                        JSON_OBJECT("status", OLD.status), 
                        JSON_OBJECT("status", NEW.status));
                END IF;
            END
        ');

        // 3. Trigger untuk Tabel RETURNS (Pengembalian)
        DB::unprepared('
            CREATE TRIGGER after_returns_insert AFTER INSERT ON returns
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, new_data)
                VALUES ("RETURNS", "INSERT", "returns", NEW.id, JSON_OBJECT("loan_id", NEW.loan_id, "status", NEW.status));
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_items_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_items_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_loans_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_loans_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_returns_insert');
    }
};
