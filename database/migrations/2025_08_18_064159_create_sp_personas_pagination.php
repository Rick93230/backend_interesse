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
                'data', 
                    COALESCE(
                        (SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id', sub.id,
                                'nombre', sub.nombre_completo,
                                'telefonos', sub.telefonos,
                                'direcciones', sub.direcciones,
                                'created_at', sub.created_at,
                                'updated_at', sub.updated_at
                            )
                        )
                        FROM (
                            SELECT 
                                p.id,
                                CONCAT_WS(' ', p.nombre, p.paterno, COALESCE(p.materno, '')) as nombre_completo,
                                COALESCE(
                                    (SELECT JSON_ARRAYAGG(t.numero) 
                                     FROM telefonos t 
                                     WHERE t.persona_id = p.id), 
                                    JSON_ARRAY()
                                ) as telefonos,
                                COALESCE(
                                    (SELECT JSON_ARRAYAGG(
                                        CONCAT(
                                            d.calle, 
                                            CASE WHEN d.numero_exterior IS NOT NULL 
                                                 THEN CONCAT(' ', d.numero_exterior) 
                                                 ELSE '' END,
                                            CASE WHEN d.numero_interior IS NOT NULL 
                                                 THEN CONCAT(' ', d.numero_interior) 
                                                 ELSE '' END,
                                            CASE WHEN d.colonia IS NOT NULL 
                                                 THEN CONCAT(' ', d.colonia) 
                                                 ELSE '' END,
                                            CASE WHEN d.cp IS NOT NULL 
                                                 THEN CONCAT(' CP ', d.cp) 
                                                 ELSE '' END
                                        )
                                    ) 
                                    FROM direcciones d 
                                    WHERE d.persona_id = p.id), 
                                    JSON_ARRAY()
                                ) as direcciones,
                                p.created_at,
                                p.updated_at
                            FROM personas p
                            ORDER BY p.id
                            LIMIT v_limit OFFSET v_offset
                        ) as sub
                        ), 
                        JSON_ARRAY()
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