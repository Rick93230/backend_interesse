<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSpProcessImport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        
        DB::unprepared("
            CREATE PROCEDURE sp_process_import_data()
            BEGIN
                DECLARE done INT DEFAULT 0;

                DECLARE v_id BIGINT;
                DECLARE v_nombre VARCHAR(255);
                DECLARE v_paterno VARCHAR(255);
                DECLARE v_materno VARCHAR(255);
                DECLARE v_telefono VARCHAR(255);
                DECLARE v_calle VARCHAR(255);
                DECLARE v_numero_exterior VARCHAR(255);
                DECLARE v_numero_interior VARCHAR(255);
                DECLARE v_colonia VARCHAR(255);
                DECLARE v_cp VARCHAR(255);

                DECLARE v_persona_id BIGINT;

                DECLARE cur CURSOR FOR
                    SELECT id, nombre, paterno, materno, telefono, calle, numero_exterior, numero_interior, colonia, cp
                    FROM temp_import;

                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

                OPEN cur;

                read_loop: LOOP
                    FETCH cur INTO v_id, v_nombre, v_paterno, v_materno, v_telefono, v_calle,
                                    v_numero_exterior, v_numero_interior, v_colonia, v_cp;
                    IF done = 1 THEN
                        LEAVE read_loop;
                    END IF;

                    SET v_persona_id = (
                        SELECT p.id
                        FROM personas p
                        WHERE p.nombre = v_nombre
                        AND p.paterno = v_paterno
                        AND ((p.materno = v_materno) OR (p.materno IS NULL AND v_materno IS NULL))
                        LIMIT 1
                    );

                    IF v_persona_id IS NULL THEN
                        INSERT INTO personas (nombre, paterno, materno, created_at, updated_at)
                        VALUES (v_nombre, v_paterno, v_materno, NOW(), NOW());
                        SET v_persona_id = LAST_INSERT_ID();
                    END IF;

                    IF v_telefono IS NOT NULL AND v_telefono <> '' THEN
                        IF NOT EXISTS (
                            SELECT 1
                            FROM telefonos t
                            WHERE t.persona_id = v_persona_id
                            AND t.numero = v_telefono
                        ) THEN
                            INSERT INTO telefonos (persona_id, numero, created_at, updated_at)
                            VALUES (v_persona_id, v_telefono, NOW(), NOW());
                        END IF;
                    END IF;

                    IF NOT EXISTS (
                        SELECT 1
                        FROM direcciones d
                        WHERE d.persona_id = v_persona_id
                        AND d.calle = v_calle
                        AND ((d.numero_exterior = v_numero_exterior) OR (d.numero_exterior IS NULL AND v_numero_exterior IS NULL))
                        AND ((d.numero_interior = v_numero_interior) OR (d.numero_interior IS NULL AND v_numero_interior IS NULL))
                        AND ((d.colonia = v_colonia) OR (d.colonia IS NULL AND v_colonia IS NULL))
                        AND ((d.cp = v_cp) OR (d.cp IS NULL AND v_cp IS NULL))
                    ) THEN
                        INSERT INTO direcciones (persona_id, calle, numero_exterior, numero_interior, colonia, cp, created_at, updated_at)
                        VALUES (v_persona_id, v_calle, v_numero_exterior, v_numero_interior, v_colonia, v_cp, NOW(), NOW());
                    END IF;

                END LOOP;

                CLOSE cur;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_process_import_data');
    }
}