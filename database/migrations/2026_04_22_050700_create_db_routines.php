<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Function: Get Total Unpaid Fines for a User
        DB::unprepared('DROP FUNCTION IF EXISTS fn_total_denda_user');
        DB::unprepared("
            CREATE FUNCTION fn_total_denda_user(u_id INT) 
            RETURNS DECIMAL(10,2)
            DETERMINISTIC
            BEGIN
                DECLARE total DECIMAL(10,2);
                SELECT IFNULL(SUM(amount), 0) INTO total 
                FROM fines 
                WHERE user_id = u_id AND status = 'unpaid';
                RETURN total;
            END
        ");

        // 2. Stored Procedure: Approve Loan and Update Stock
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_approve_peminjaman');
        DB::unprepared("
            CREATE PROCEDURE sp_approve_peminjaman(IN l_id INT, IN w_id INT)
            BEGIN
                DECLARE v_loan_date DATE;
                SELECT loan_date INTO v_loan_date FROM loans WHERE id = l_id;

                -- Tentukan status berdasarkan tanggal (Booked jika besok, Borrowed jika hari ini)
                IF v_loan_date > CURDATE() THEN
                    UPDATE loans SET status = 'booked', approved_by = w_id, approved_at = NOW() WHERE id = l_id;
                ELSE
                    UPDATE loans SET status = 'borrowed', approved_by = w_id, approved_at = NOW() WHERE id = l_id;
                END IF;

                -- Selalu potong stok barang saat pertama kali di-approve
                UPDATE items i
                JOIN loan_items li ON i.id = li.item_id
                SET i.stock = i.stock - li.quantity
                WHERE li.loan_id = l_id;
            END
        ");

        // 3. Stored Procedure: Complete Return and Restock
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_selesaikan_pengembalian');
        DB::unprepared("
            CREATE PROCEDURE sp_selesaikan_pengembalian(IN l_id INT, IN r_id INT, IN w_id INT)
            BEGIN
                -- Update Loan & Return Status
                UPDATE loans SET status = 'completed' WHERE id = l_id;
                UPDATE returns SET status = 'completed', user_id = w_id WHERE id = r_id;

                -- Update Item Stock (Increase back)
                UPDATE items i
                JOIN loan_items li ON i.id = li.item_id
                SET i.stock = i.stock + li.quantity
                WHERE li.loan_id = l_id;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_total_denda_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_approve_peminjaman');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_selesaikan_pengembalian');
    }
};
