<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Triggers for REPAIRS
        DB::unprepared('
            CREATE TRIGGER after_repairs_insert AFTER INSERT ON repairs
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, new_data)
                VALUES ("REPAIRS", "INSERT", "repairs", NEW.id, JSON_OBJECT("item_id", NEW.item_id, "status", NEW.status));
            END
        ');

        DB::unprepared('
            CREATE TRIGGER after_repairs_update AFTER UPDATE ON repairs
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO activity_logs (log_type, action, table_name, record_id, old_data, new_data)
                    VALUES ("REPAIRS", "STATUS_CHANGE", "repairs", NEW.id, 
                        JSON_OBJECT("status", OLD.status), 
                        JSON_OBJECT("status", NEW.status));
                END IF;
            END
        ');

        // Triggers for FINES
        DB::unprepared('
            CREATE TRIGGER after_fines_insert AFTER INSERT ON fines
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, new_data)
                VALUES ("FINES", "INSERT", "fines", NEW.id, JSON_OBJECT("user_id", NEW.user_id, "amount", NEW.amount));
            END
        ');

        DB::unprepared('
            CREATE TRIGGER after_fines_update AFTER UPDATE ON fines
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO activity_logs (log_type, action, table_name, record_id, old_data, new_data)
                    VALUES ("FINES", "STATUS_CHANGE", "fines", NEW.id, 
                        JSON_OBJECT("status", OLD.status), 
                        JSON_OBJECT("status", NEW.status));
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER after_fines_delete AFTER DELETE ON fines
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (log_type, action, table_name, record_id, old_data)
                VALUES ("FINES", "DELETE", "fines", OLD.id, JSON_OBJECT("user_id", OLD.user_id, "amount", OLD.amount));
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_repairs_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_repairs_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_fines_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_fines_update');
        DB::unprepared('DROP TRIGGER IF EXISTS after_fines_delete');
    }
};
