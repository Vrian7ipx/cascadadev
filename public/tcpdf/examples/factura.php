<?php
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
require_once('barcodes/tcpdf_barcodes_2d_include.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		$image_file = K_PATH_IMAGES.'asd.png';
		$this->Image($image_file, 5, -2, 35, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Roy');
$pdf->SetTitle('Factura');
$pdf->SetSubject('Primera Factura');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set margins
$pdf->SetMargins(5, PDF_MARGIN_TOP, 9);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', 'B' , 11);
$nit = 7024739019;
$nfac = 001;
$nauto = 1234567890123;
// add a page
$pdf->AddPage();
//cuadro roy
$html = '
<p>
NIT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$nit.' <br>
Nº FACTURA &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	: '.$nfac.' <br>
Nº AUTORIZACION &nbsp;: '.$nauto.'  <br>
</p>					';

$pdf->writeHTMLCell($w=0, $h=0, $x='133', $y='8', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
// Rounded rectangle
$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$pdf->RoundedRect(130, 5, 75, 22, 2, '1111', null);

///title roy
$pdf->SetFont('times', 'B', 18);
$html2='<h1>FACTURA</h1>';
$pdf->writeHTMLCell($w=0, $h=0, $x='75', $y='18', $html2, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);

//nombre de la empresa roy

$pdf->SetFont('times', 'B', 12);
$datos = '
<p style="line-height: 150% ">
Roy Corp
</p>';
$pdf->writeHTMLCell($w=0, $h=0, $x='10', $y='35', $datos, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//original scf-1 roy

$pdf->SetFont('times', 'B', 12);
$datos2 = '
<p style="line-height: 150% ">
ORIGINAL  SFC-1
</p>';
$pdf->writeHTMLCell($w=0, $h=0, $x='145', $y='28', $datos2, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);

//datos de la empresa roy
$sucursal = "Casa Matriz";
$direccion = "Calle de las Rosas Nº 100";
$ciudad = "La Paz - Bolivia";

$pdf->SetFont('times', '', 10);
$datos = '
<p>
'.$sucursal.'<br>
Dirección<br>
'.$direccion.'<br>
'.$ciudad.'<br>
</p>					';

$pdf->writeHTMLCell($w=0, $h=0, $x='10', $y='42', $datos, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//actividad económica roy
$actividad = "Venta de Productos";
$pdf->SetFont('times', 'B', 11);
$act = '
<p>
Actividad Económica : '.$actividad.'<br>
</p>';

$pdf->writeHTMLCell($w=0, $h=0, $x='130', $y='35', $act, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//TABLA 1 roy
$pdf->SetFont('times', '', 11);
$fecha = "09 de Octubre de 2015";
$señor = "Cascada";
$nit = 20245475018;
$tbl = <<<EOD
<table cellspacing="0" cellpadding="5" border="1">
    <tr>
        <td><b>Fecha :</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$fecha <br/><b>Señor(es):</b>&nbsp;&nbsp; $señor  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>NIT/CI : $nit</b></td>
    </tr>
</table>
EOD;

$pdf->writeHTMLCell($w=0, $h=0, $x='10', $y='65', $tbl, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);

/////tabla 2 roy

// test all combinations of alignments
$text = '';
$text .= '
<thead>
 <tr>
  <td width="70" align="center"><b>CANTIDAD</b></td>
  <td width="240" align="center"><b>CONCEPTO</b></td>
  <td width="130" align="center"><b>PRECIO UNITARIO</b></td>
  <td width="112" align="center"> <b>SUBTOTAL</b></td>
 </tr>
</thead>
';

for ($i = 0; $i < 38; ++$i) {
		$text .='
		<tr>
		<td width="70" align="center">'.$i.'</td>
		<td width="240" align="center">100</td>
		<td width="130" align="center">252.00</td>
		<td width="112" align="center"> 252.00</td>
		</tr>
		';
}

		$text .='
		<tr>
		<td colspan="3" align="rigth"><b>Total Bs.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
		<td width="112" align="center"><b>252.00</b></td>
		</tr>
		';
$text2 = '<table border="1" cellpadding="3" cellspacing="0">
			'.$text.'
		</table>
		';
		
		//$pdf->Image('images/image_demo.jpg', $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, $fitbox, false, false);
$pdf->writeHTMLCell($w=0, $h=0, 10, 84, $text2, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//total en literal

$cod = $y+46+$i*6.85;
$total = '<br>
<b>Son:</b> DOS CIENTOS CINCUENTA Y DOS 00/100.....BOLIVIANOS.<br>
';
$pdf->writeHTMLCell($w=0, $h=0, $x=10, '', $total, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);


//final roy
$final = '
<table border="0">
<tr>
	<td width="460" align="left"><b>CODIGO DE CONTROL :&nbsp;&nbsp;&nbsp;&nbsp; BF-86-6B-98 </b></td>
	<td width="92" rowspan="6">
</td>
</tr>
<tr>
	<td width="460" align="left"><b>Fecha Limite de Emisión : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;28/12/2015 </b></td>
</tr>
<tr>
<td>
</td>
</tr>
<tr>
	<td width="460" align="center"><b>"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAIS, EL USO ILICITO DE ESTA SERA SANCIONADO DE ACUERDO A LEY"</b></td>
</tr>
<tr><td></td></tr>
<tr>
	<td width="460" align="center" style="font-size:10px">"Ley Nº 453: Está prohibido importar, distribuir o comercializar productos expirados o prontos a expirar"</td>
</tr>
</table>
';
if ( $i == 17 || $i == 18 || $i == 19 || $i == 20 ){
	$pdf->writeHTMLCell($w=0, $h=0, $x=10, $pdf->GetY()+25, $final, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
}else{
	$pdf->writeHTMLCell($w=0, $h=0, $x=10, '', $final, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
}

//$left_column = $pdf->write2DBarcode('7024736019|3|2104002021751|2015-10-02|30.50|30.50|15451254|0|0|0|0', 'QRCODE,L', '175', '', 30, 30, '', 'N');
//$pdf->writeHTMLCell(80, '', '', '', $left_column, 0, 0, 0, true, 'J', true);






//qr roy
$pdf->write2DBarcode('7024736019|3|2104002021751|2015-10-02|30.50|30.50|15451254|0|0|0|0', 'QRCODE,L', '175', 
$pdf->GetY()-25, 25, 25, '', 'N');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_003.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+