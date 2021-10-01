<?php
namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\DB;

class PDF extends Fpdf {
    private $height = 5;

    public function hojaInscripcion($data, $subjects, $positionY = 0, $isParer = false)
    {
      $date = date('d/m/Y', strtotime($data->created));
      // llamando a el encabezado de la asesoria
      $this->getHeader('hoja de inscripcion', $data, $positionY);
      // tabla de materias inscriptas
      $this->tableEnrolled($subjects, $date);
      // reset del grosor de las lineas
      $this->SetLineWidth(0.4);
      // tabla de las firmas y observacion
      $this->getFirmas( $isParer);
    }

    public function hojaPago($data, $subjects, $pago, $positionY = 0, $isParer = false)
    {
      $date = date('d/m/Y', strtotime($data->created));
      // llamando a el encabezado de la asesoria
      $this->getHeader('hoja de inscripcion', $data, $positionY);
      // tabla de materias inscriptas
      $this->tableEnrolled($subjects, $date);

      //tabla de los pagos
      $this->tablePago($pago);

      // table aranceles
      $this->tableAranceles($pago);
    }

    public function tableAranceles($pago)
    {
      $total = 0.0;
      $this->SetFont('Arial','B', 12);
      $this->Ln(10);
      $this->Cell(0, $this->height + 2, 'Aranceles', 'B', 1, 'C');
      $this->Ln(5);

      $this->Cell(15, $this->height + 1, '#', 1, 0, 'C');
      $this->Cell(140, $this->height + 1, 'Descripcion', 1, 0, 'C');
      $this->Cell(0, $this->height + 1, 'Monto', 1, 1, 'C');

      $this->SetFont('Arial','', 12);
      $aranceles = $pago->aranceles;
      foreach ($aranceles as $key => $value) {
        $item = DB::table('aranceles')
          ->where('idarancel', $value->arancel_id)
          ->select('descripcion')
          ->first();

        $this->Cell(15, $this->height + 1, ($key + 1), 1, 0, 'C');
        $this->Cell(140, $this->height + 1, utf8_decode(strtoupper($item->descripcion)), 1, 0, 'L');
        $this->Cell(0, $this->height + 1,  "$ ".number_format($value->precio, 2, '.') , 1, 1, 'C');
        $total += $value->precio;
      }
      $this->SetFont('Arial','B', 13);
      $this->Cell(155, $this->height + 2, 'Total', 1, 0, 'L');
      $this->Cell(0, $this->height + 2,  "$ ".number_format($total, 2, '.') , 1, 0, 'C');
    }

    public function tablePago($pago)
    {
      $this->SetFont('Arial','B', 12);
      $this->Ln(10);
      $this->Cell(0, $this->height + 2, 'Informacion', 'B', 1, 'C');
      $this->Ln(5);

      $this->SetFont('Arial','B', 12);
      $this->Cell(80,  $this->height + 1,
        $pago->banco->is_referido === 0 ?
          'Nombre del titular' :
          '# referencia',
        1, 0, 'L'
      );

      $this->Cell(40, $this->height + 1, 'Monto', 1, 0, 'C');
      $this->Cell(42, $this->height + 1, 'Banco', 1, 0, 'C');
      $this->Cell(0, $this->height  + 1, 'Fecha', 1, 1, 'C');

      $this->SetFont('Arial','', 11);
      $this->Cell(80,  $this->height + 2, $pago->nombre_titular, 1, 0, 'L');
      $this->Cell(40,  $this->height + 2, "$ ".number_format($pago->monto, 2, '.') , 1, 0, 'C');
      $this->Cell(42,  $this->height + 2, $pago->banco->nombre, 1, 0, 'C');
      $this->Cell(0,  $this->height + 2, date('d/m/Y', strtotime($pago->fecha_pago) ) , 1, 1, 'C');

      $this->SetFont('Arial','B', 12);
      $this->Cell(0, $this->height + 1, 'Concepto', 1, 1, 'L');
      $this->SetFont('Arial','', 11);
      $this->Cell(0, $this->height + 1, utf8_decode($pago->concepto), 1, 0, 'L');
    }

    public function getHeader($title = '', $data, $positionY = 5, $positionX = 5)
    {
      // variables
      $explode = explode("-", $data->ciclo);
      $fullNames = $data->apellidos." ".$data->nombres;
      // ==========================================
      $this->Image(public_path('images/').'utla.png', $positionX, $positionY, 16);

      $this->SetFont('Arial','',12);
      $this->Cell(0, $this->height, "ADACAD-PRC-02-FOR-04", 0, 1, 'R');
      $this->SetFont('Arial','B',16);
      $this->Cell(0, $this->height, "UNIVERSIDAD TECNICA LATINOAMERICANA", 0, 1, 'C');
      $this->Cell(0, $this->height,  strtoupper(utf8_decode($title)) , 0, 1, 'C');

      $this->SetFont('Arial','', 13);
      $this->SetXY(-30, $positionY + 6);
      $this->Cell(20, $this->height + 2,  utf8_decode("Año: ".$explode[1]), 0, 1, 'L' );
      $this->SetXY(-30, $positionY + $this->height + 6);
      $this->Cell(20, $this->height + 2,  utf8_decode("Ciclo: ".$explode[0]), 0, 1, 'L' );

      // informacion del estudiante
      $this->SetFont('Arial','B', 11);
      $this->Ln(5);
      $this->Cell(95, $this->height, strtoupper(utf8_decode($fullNames)), 'B', 0, 'L');
      $this->Cell(6);
      $this->Cell(60, $this->height, strtoupper(utf8_decode($data->nomcarrera)), 'B', 0, 'C');
      $this->Cell(6);
      $this->Cell(0, $this->height, $data->carnet, 'B', 1, 'C');

      $this->SetFont('Arial','', 12);
      $this->Cell(90, $this->height, 'Nombre del Alumno', 0, 0, 'C');
      $this->Cell(8);
      $this->Cell(60, $this->height, 'Carrera', 0, 0, 'C');
      $this->Cell(6);
      $this->Cell(0, $this->height, utf8_decode('N° de Carnet') , 0, 1, 'C');
      $this->Ln(5);
    }

    public function tableEnrolled($data, $date)
    {
      $this->SetFont('Arial','B', 9);
      $this->Cell(15, $this->height, 'CODIGO', 1, 0, 'C');
      $this->Cell(73, $this->height, 'ASIGNATURA', 1, 0, 'C');
      $this->Cell(12, $this->height, 'INSCR.', 1, 0, 'C');
      $this->Cell(16, $this->height, 'FECHA', 1, 0, 'C');
      $this->Cell(30, $this->height, 'TURNO', 1, 0, 'C');

      $this->Cell(15, $this->height, 'RETIRO', 1, 0, 'C');
      $this->Cell(15, $this->height, 'ESTADO', 1, 0, 'C');

      $this->Cell(10, $this->height, 'LAB', 1, 0, 'C');
      $this->Cell(0, $this->height, 'NOTA', 1, 1, 'C');

      foreach ($data as $value) {
        $this->rowInscripcion($value, $date);
      }
    }

    public function getFirmas($isParer)
    {
      $this->Ln(6);
      $this->Cell(50, $this->height, '', 'B');
      $this->Cell(10);
      $this->Cell(55, $this->height, '', 'B');
      $this->Cell(10);
      $this->Cell(35, $this->height * 2, 'Sello: ', 0, 0, 'L');
      $this->Cell(5);
      $this->Cell(13, $this->height, 'Fecha: ', 0);
      $this->Cell(0, $this->height, date('d/m/Y'), 'B', 1, 'C');

      $this->Cell(50, $this->height, 'Firma del Alumno', 0, 0, 'C');
      $this->Cell(10);
      $this->Cell(55, $this->height, 'Receptor de Hoja de Inscripcion', 0, 0, 'C');

      $this->Ln(10);
      $this->Cell(35, $this->height, 'OBSERVACIONES: ', 0, 0);
      $this->Cell(0, $this->height, '', 'B', 1);
      $this->Cell(0, $this->height, '', 'B', 1);
      $this->Cell(0, $this->height, '', 'B', 1);
    }

    public function rowInscripcion($item, $date)
    {
      $this->SetFont('Arial','', 8);
      // row 1
      $this->Cell(15, $this->height, $item->codmate, 'RL');
      $this->Cell(73, $this->height, utf8_decode(strtoupper($item->nommate)) , 'R');
      $this->Cell(12, $this->height, $item->tipoinscri, 'R');
      $this->Cell(16, $this->height, $date, 'R');

      $this->Cell(5, $this->height, $item->turno, 0);
      $this->Cell(18, $this->height, $item->dias, 0);
      $this->Cell(7, $this->height, $item->aula, 'R');

      $this->Cell(15, $this->height, '', 'R');
      $this->Cell(15, $this->height, 'ACTIVO', 'R', 0, 'C');
      $this->Cell(10, $this->height, $item->lab, 'R', 0, 'C');
      $this->Cell(0, $this->height, '', 'R', 1, 'C');

      // row 2
      $this->Cell(15, $this->height + 2, '', 'RLB');
      $this->Cell(73, $this->height + 2, '', 'RB');
      $this->Cell(12, $this->height + 2, '', 'RB');
      $this->Cell(16, $this->height + 2, '', 'RB');
      $this->Cell(30, $this->height + 2, $item->hora, 'RB', 0, 'C');

      $this->Cell(15, $this->height  + 2, '', 'RB');
      $this->Cell(15, $this->height  + 2, '', 'RB');
      $this->Cell(10, $this->height  + 2, '', 'RB');
      $this->Cell(0, $this->height   + 2, '', 'RB', 1);
    }
  }
?>