<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSpPersonasPagination extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       DB::unprepared("
            CREATE PROCEDURE sp_process_personas_pagination(IN p_pagina INT)
            BEGIN
                DECLARE v_limit INT DEFAULT 100;
                DECLARE v_offset INT;
                DECLARE v_total INT;
                DECLARE v_total_pages INT;

                SET v_offset = (p_pagina - 1) * v_limit;

                SELECT COUNT(*) INTO v_total FROM personas;

                SET v_total_pages = CEIL(v_total / v_limit);

                SELECT JSON_OBJECT(
                    'data', (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', id,
                                'nombre', nombre,
                                'paterno', paterno,
                                'materno', materno,
                                'created_at', created_at,
                                'updated_at', updated_at
                            )
                        )
                        FROM (
                            SELECT id, nombre, paterno, materno, created_at, updated_at
                            FROM personas
                            ORDER BY id
                            LIMIT v_limit OFFSET v_offset
                        ) AS sub
                    ),
                    'meta', JSON_OBJECT(
                        'total_registros', v_total,
                        'total_paginas', v_total_pages,
                        'pagina_actual', p_pagina
                    )
                ) AS resultado;
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_process_personas_pagination');
    }
}