<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExcelUploadController extends Controller
{
    public function upload_excel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $path = $file->storeAs('temp_imports', 'import_' . time() . '.' . $file->getClientOriginalExtension());

        try {
            $csvPath = $this->convertToCsv(storage_path('app/' . $path));
            DB::table('temp_import')->truncate();
            
            $this->loadDataFromCsv($csvPath);
            
            DB::unprepared('CALL sp_process_import_data()');
            
            return response()->json(['message' => 'Importación completada con éxito']);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            Storage::delete($path);
            if (isset($csvPath) && file_exists($csvPath)) {
                unlink($csvPath);
            }
        }
    }

    private function convertToCsv($excelPath)
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($excelPath);
        
        $csvPath = str_replace('.xlsx', '.csv', $excelPath);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->save($csvPath);
        
        return $csvPath;
    }

    private function loadDataFromCsv($csvPath)
    {
        $this->enable_local_infile();
        $query = sprintf(
            "LOAD DATA LOCAL INFILE '%s' 
            INTO TABLE temp_import 
            FIELDS TERMINATED BY ',' 
            ENCLOSED BY '\"' 
            LINES TERMINATED BY '\\r\\n' 
            IGNORE 1 LINES 
            (nombre, paterno, materno, telefono, calle, numero_exterior, numero_interior, colonia, cp)",
            addslashes($csvPath)
        );
        
        DB::connection()->getPdo()->exec($query);
    }

    private function enable_local_infile(){
        $query_local_infile = sprintf("SET GLOBAL local_infile = 1");
        DB::connection()->getPdo()->exec($query_local_infile);
        return true;
    }
}
