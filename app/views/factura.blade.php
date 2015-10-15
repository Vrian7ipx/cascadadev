<?php
// Include the main TCPDF library (search for installation path).
require_once(app_path()."/includes/tcpdf/tcpdf.php");
require_once(app_path()."/includes/tcpdf/examples/tcpdf_include.php");
require_once(app_path()."/includes/tcpdf/examples/barcodes/tcpdf_barcodes_2d_include.php");


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('dejavusans', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Pag '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ipxserver');
$pdf->SetTitle('Factura');
$pdf->SetSubject('Primera Factura');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set margins
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// borra la linea de arriba en el area del header
$pdf->setPrintHeader(false); 
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->setFontSubsetting(false);
$pdf->SetFont('times', 'B' , 11);
$nit = $invoice->nit;
$nfac = $invoice->invoice_number;
$nauto = $invoice->number_autho;
$sfc = "01";
// add a page
$pdf->AddPage();
//contenido del recuadro
$html = '
    <p style="line-height: 175% ">
        NIT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$nit.' <br>
        AUTORIZACIÓN Nº  &nbsp;: '.$nauto.'  <br>
        FACTURA Nº &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$nfac.' <br>
        SFC &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: '.$sfc.' <br>
    </p>';
//imprime el contenido de la variable html
$pdf->writeHTMLCell($w=0, $h=0, $x='130', $y='5', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//dibuja un rectangulo
$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$pdf->RoundedRect(126, 5, 75, 28, 2, '1111', null);

//logo 
$imgdata = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAGQAAABvCAIAAACVR/LAAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDIxIDc5LjE1NTc3MiwgMjAxNC8wMS8xMy0xOTo0NDowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDM1NTYxN0Q3QUZGMTFFNDk0RkNFMDRDMjE0QkY4MTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDM1NTYxN0M3QUZGMTFFNDk0RkNFMDRDMjE0QkY4MTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgV2luZG93cyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJGMTM4QTlDNTZBNUZEOTZGNTExQ0EyRjkzRjA3OEFCRCIgc3RSZWY6ZG9jdW1lbnRJRD0iRjEzOEE5QzU2QTVGRDk2RjUxMUNBMkY5M0YwNzhBQkQiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7K1ajaAAAoa0lEQVR42uzdd9hU1fkuYCScJMaYRMXYsVfsBUEUg1FjjwWxYY29YIko9l6wK4oVFbEhYsEOYiyIGAvYgy1K1KPGGI0lzSS/+3xPXGc7M19DNJ7rZP8x1549e6/yvO1511p7zQz/+te/Ovz3aNvR8b8Q/Besr+To1PLPn3766d///veOHTsWa51hhhmc5/PfeHfsWL7m09f8lK9u8PnPf/6z/OR8hqajVJQyHdW6qr+WR5yUm+vvrFbtSNXla36tqbd6rrM/+MEPOnXqVL2ndbDA9Oabb/7xj3/805/+VCooReQkNX3rW9+qtrW5DlSbWzDNxX/84x9/bzo+++wz59US3PO/mo5OTYe6irSmo8qkTMh+/PHHr7322kILLbTOOusUoJsFK+345JNPJk6c+N57702ZMuWNN97QxDxZulqwqzY9elGUqAbTql5A5K9//etHH31EJN/5zne++93v+pxppplmnnnmGWecsSoSCJKW9vzlL3/xiPtVkdtgB8SqVgb6qmir6txQm2p+0gzVjRo1aurUqbvttlu1s//n5npF0JOxY8c+99xzTzzxxKuvvlr6WaNfNVrW8KeGR7qkw/PNN9+Pf/zjLl26LLDAAnPNNZfzWWedtdq9NBRGxPb222/T9Jdffvndd991ToTurBd+Q9m3Ub8CBTEo+YMPPjjvvPNWXXXVagkNzFCbxowZ8/rrr2vQDJWjxh9VhVPVr7iwfObKP5qO6PnCCy+sBUsttdSiiy4622yzBTj9f//993/zm9/QNYZPiYo3hOmPfvQjn3BcYoklNtlkk5T50ksv0fpHH33UU6CMp6NoTmrgbujRmvvJRbLxSZdvueWWbt26VR9pABY9pFNanBZUrayKV1X5C6A1TeGD4pt5gTXWWGP11Veff/75dYncnn/++WeffZb+6iqkVNeCCqQKqIFMCV27dl1yySU33HDDDTbYwOMvvPDCww8//Ktf/YrNRk4No0Q9QPU/efzb3/62ZmuzkmsUs1NDb6fW6tcofI0DqnYjoarGlqNKhNO3b19KwR1QnJEjRz7yyCOUIlUEaypG0Tp37iwSff/73+fCoiDu+fOf/0zI0IzpQfmVV1657777PMtyl19++d69e6+55poksfvuuzOIG2644cMPP/R4QkF7PX017LLH1qOh+/hOvW1OJvVBrXoeK9DclVZaaeedd6YCf/vb35566ile87HHHksJlIslLr744ostthiHNeecc/LuCSP18TTazTpYKOfwctPBDP9303HXXXdBmYpRtG233ZZgRowYceedd/pJUSSUkNJeyBoHzfofGMiee+7JLrS+qjI1hlb1+iX0prSll156++2355soAuu48cYbNT1OHUarrbbasssuC6NSQnvdsJsp6eTJk59++mluC4J5XLzffPPNyUCzuedx48bRym81HW3HK/LW4J/+9KfnnHNONYY0AIsfARZJBqyawFftVdCJjwis3/ve94i3X79+rt/UdLz11luu06Du3bv36tWL86ovZ9qOaBw4Jk2aRCSsm4BdIYytttpqueWWY+yXXnrp448/rnmFZEx/sPbYYw9gBYvmFKp8Dabu5J7222+/BRdccMKECRdddBGBu8gMN9tsMx3gj6Yvk6zqI6fBMFnf6NGjc33jjTfef//9/SSoXXbZZXCsxsppA6uxz6p3UvVxt8oMfO6444677rqrk5NPPpkf8RMHvPXWW6+99tpgmu6cu6Y9BBYPyHlxjjz9bbfdxkWSOsPkEI477jieTs+542keaOnYqpP71+dHwyTLufgFIMGIF2eAxOunLbfc8txzz6VTkGqVpk4v7GAh7B566KEnnHDCIossIoAee+yxgwcPFkBouriZCNtqY5q7oVMLSCWOlghVE/tcx0fQ7rPOOkvLhg0bdvnll7uClzNGdtcwT/w6xgY6deIcV1hhBdbHBm+++WZE7MgjjzzqqKPmnnvuq6++Gl7TFiU7topuUaVq9hvrm2eeeYYMGQIpkiQ6FOEnP/mJKz179mxL3lPDbkL0P2s6qnl1OdrevURePov1zTLLLFh+//79hc5ddtlln332UY6Sp0HZOzUXZYoq1bOepMriGkOjWYcccshDDz3k4k477ST5bDVfSyHpfHD5Z9MRyArLTbQtZDgMgNZ0/PyojnY0lLR78FW55ymnnMJhMc+jjz66T58+fj3//PM1AFlv6O+bE0mnlgNzdSCl0CifQt6FF14o/xg4cOD9999PqwcMGCAAtSyuDMVQwJrRmNCOKmTlPJiGjmuA7jn/zudHeGxQa1i1i4R6+umnwwsjA9bhhx++xRZbaMDFF1+sJUJk242xU8uJUg1k+dRiHh1SlBzB0dzDDjtMGGoBd0REy3xqZQqkI9KaQOO6tOavnx/BMWAlfiXyVkEJp2NiqL/2lOGahgxOU/ksvuLXv/71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');
$pdf->Image('@'.$imgdata, '14', '18', 30, 30, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
///title
$pdf->SetFont('dejavusans', 'B', 20);

if(Auth::user()->branch_id==1)
{
    $factura = "FACTURA";
    $tercero ="";
}
else{
$factura = "FACTURA POR TERCEROS";
$tercero = Auth::user()->branch->name;
}

$titleFactura='<table>
<tr>
<td align="center"><font color="#333333">'.$factura.'</font></td>
</tr>
</table>';
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='50', $titleFactura, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);

//nombre de la empresa

$pdf->SetFont('dejavusans', 'B', 22, false);
$NombreEmpresa = '
    <p style="line-height: 150%">
        <font color="#333333">
            LA CASCADA S.A.
        </font>
    </p>';
$pdf->writeHTMLCell($w=0, $h=0, $x='4', $y='5', $NombreEmpresa, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
$pdf->SetFont('dejavusans', 'B', 10, false);
$pdf->writeHTMLCell($w=0, $h=0, $x='15', $y='1', $tercero, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//original scf-1 roy
$pdf->SetFont('dejavusans', 'B', 12);
$original = '
    <p style="line-height: 150% ">
        ORIGINAL
    </p>';
$pdf->writeHTMLCell($w=0, $h=0, $x='155', $y='34', $original, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);

//datos de la empresa
$casa = $matriz->name;
$dir_casa = $matriz->address2." - ".$matriz->address1;
$tel_casa = $matriz->postal_code;
$city_casa = $matriz->city." - Bolivia";

$sucursal = $invoice->branch;
$direccion = $invoice->address2." ".$invoice->address1;
$ciudad = $invoice->city." - Bolivia";
$telefonos =$invoice->work_phone;


$pdf->SetFont('dejavusans', '', 8);
$datoEmpresa = '
    <table border = "0">
        <tr>
        <td width="220" align="left">'.$casa.'</td>
        </tr>
        <tr>
        <td width="220" align="left">'.$dir_casa.'</td>
        </tr>
        <tr>
        <td width="220" align="left">Telfs: '.$tel_casa.'</td>
        </tr>
        <tr>
        <td width="220" align="left">'.$ciudad.'</td>
        </tr>
        <tr>
        <td width="220" align="left">'.$sucursal.'</td>
        </tr>
        <tr>
        <td width="220" align="left">'.$direccion.'</td>
        </tr>
        <tr>
        <td width="220" align="left">Telfs: '.$telefonos.'</td>
        </tr>
        <tr>
        <td width="220" align="left">'.$ciudad.'</td>
        </tr>
    </table>				';
$pdf->writeHTMLCell($w=0, $h=0, $x='44', $y='18', $datoEmpresa, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//actividad económica
$actividad=$invoice->activity_pri;
$pdf->SetFont('dejavusans', '', 10);
$actividadEmpresa = '
    <table>
        <tr>
            <td align="center">'.$actividad.'</td>
        </tr>
    </table>';

$pdf->writeHTMLCell($w=0, $h=0, $x='130', $y='40', $actividadEmpresa, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//TABLA datos del cliente

$pdf->SetFont('dejavusans', '', 11);
//$date_for =new date($invoice->invoice_date);
setlocale(LC_ALL,"es_ES");
$string = $invoice->invoice_date;
$date =date("d/m/Y", strtotime($invoice->invoice_date));
$date = DateTime::createFromFormat("d/m/Y", $date);

$date_for=strftime("%d de %B de %Y",$date->getTimestamp());
        

//$date_for->format('Y-m-d');
//setlocale(LC_ALL,"es_ES");
//$year = substr($date_for, 1,4);

$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
 
$fecha= $invoice->state.", ".$date_for;

//$fecha = $date_for->format('Y-m-d H:i:s');

$señor = $invoice->name;
$nit = $invoice->nit;
$datosCliente = <<<EOD
<table cellpadding="2" border="">
    <tr>
        <td width="300"><b>&nbsp;Lugar y fecha :</b>$fecha </td>
        <td width="220" align="right"><b>NIT/CI :</b>$nit</td>
    </tr>
    <tr>
        <td colspan="3"><b>&nbsp;Señor(es):</b> $señor </td>
    </tr>
</table>
EOD;

$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='62', $datosCliente, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);

//dibuja rectangulo datos del cliente
$pdf->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$pdf->RoundedRect(16, 62, 184, 14, 1, '1111', null);
$textTitulos = "";
$textTitulos .= '<p></p>
<table border="0.2" cellpadding="3" cellspacing="0">
    <thead>
        <tr>
         <td width="70" align="center" bgcolor="#E6DFDF"><font size="10"><b>CANTIDAD</b></font></td>
         <td width="240" align="center" bgcolor="#E6DFDF"><font size="10"><b>CONCEPTO</b></font></td>
         <td width="115" align="center" bgcolor="#E6DFDF"><font size="10"><b>PRECIO UNITARIO</b></font></td>
         <td width="97" align="center" bgcolor="#E6DFDF"><font size="10"><b>TOTAL</b></font></td>
        </tr>
    </thead>
</table>
';
$pdf->writeHTMLCell($w=0, $h=0, '', '', $textTitulos, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);			
//
$ini = 0;
$final = "";
$resto = $ini;
//for ($i = 0; $i < 5; ++$i) {
foreach ($products as $key => $product){
		$textContenido ='
        <table border="0.2" cellpadding="3" cellspacing="0">
		<tr>
		<td width="70" align="center"><font size="10">'.intval($product->qty).'</font></td>
		<td width="240"><font size="10">'.$product->notes.'</font></td>
		<td width="115" align="right"><font size="10">'.number_format((float)$product->cost, 2, '.', '').'</font></td>
		<td width="97" align="right"><font size="10"> '.number_format((float)$product->line_total, 2, '.', '').'</font></td>
		</tr>
         </table>
		';
        $ini = $pdf->GetY(); //punto inicial antes de dibujar la siguiente fila
        
        if(($ini+$resto)>= 250.46944444444){
            $pdf->AddPage();
            $pdf->writeHTMLCell($w=0, $h=0, '', '', $textContenido, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
        }
        else{
        $pdf->writeHTMLCell($w=0, $h=0, '', '', $textContenido, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
        $final = $pdf->GetY();  //punto hasta donde se dibujo la fila
        }
        $resto = $final-$ini; //diferencia entre $ini y $final para sacar el tamaño siguiente a dibujar
}

$texPie = "";
$subtotal = number_format((float)$invoice->subtotal, 2, '.', '');
$descuento= number_format((float)$invoice->discount, 2, '.', '');
$total = number_format((float)$invoice->amount, 2, '.', '');
$ice= number_format((float)$invoice->custom_value1, 2, '.', '');
$fiscal = number_format((float)$invoice->fiscal, 2, '.', '');

require_once(app_path().'/includes/numberToString.php');
$nts = new numberToString();
$num = explode(".", $invoice->amount);

$literal= $nts->to_word($num[0]).substr($num[1],0,2);
            
$pdf->SetFont('dejavusans', '', 11);
		$texPie .='
		<table border="0.2" cellpadding="3" cellspacing="0">
            <tr>
                <td width="425" align="right"><b>SUBTOTAL &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td  width="97" align="right"><b>'.$subtotal.'</b></td>
            </tr>
            <tr>
                <td width="425"  align="right"><b>Descuentos &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td width="97" align="right"><b>'.$descuento.'</b></td>
            </tr>
            <tr>
                <td width="425"  align="right"><b>TOTAL A PAGAR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td width="97" align="right"><b>'.$total.'</b></td>
            </tr>
            <tr>
                <td width="425"  align="right"><b>ICE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td width="97" align="right"><b>'.$ice.'</b></td>
            </tr>
            <tr>
                <td width="425"  align="right"><b>Importe Crédito Fiscal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td width="97" align="right"><b>'.$fiscal.'</b></td>
            </tr>
            <tr>
                <td colspan="4"><b>Son: </b>'.$literal.'/100.....BOLIVIANOS.</td>
            </tr>
		</table>
		';
        if ($pdf->GetY() >= '210.6375' ){

            $pdf->AddPage();
        }
$pdf->writeHTMLCell($w=0, $h=0, '', '', $texPie, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//salto de pagina	



$control_code = $invoice->control_code;
$fecha_limite = date("d/m/Y", strtotime($invoice->deadline));

$law_gen="ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAIS, EL USO ILICITO DE ESTA SERA SANCIONADO DE ACUERDO A LEY";
$law=$invoice->law;
$datosFactura = '
<table border="0">
    <tr><br>
        <td width="460" align="left"><b>CÓDIGO DE CONTROL :&nbsp;&nbsp;&nbsp;&nbsp; '.$control_code.'</b></td>
        <td width="92" rowspan="6">
    </td>
    </tr>
    <tr>
        <td width="460" align="left" style="line-height: 300%"><b>Fecha Limite de Emisión : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fecha_limite.' </b></td>
    </tr>
    <tr>
        <td width="440" align="center" style="font-size:8px"><b>"'.$law_gen.'"</b></td>
    </tr>
    <tr><td></td></tr>
    
    <tr>
        <td width="440" align="center" style="font-size:7px">"'.$law.'"</td>
    </tr>
</table>
';
if ($pdf->GetY() >= '210.6375' ){
            $pdf->AddPage();
        }
$pdf->writeHTMLCell($w=0, $h=0, '', '', $datosFactura, $border=0, $ln=1, $fill=0, $reseth=true, $align='left', $autopadding=true);
//qr roy
$date_qr =date("d/m/Y", strtotime($invoice->invoice_date));
if($descuento=="0.00")
    $descuento=0;
$datosqr = '1006909025|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$date_qr.'|'.$total.'|'.$fiscal.'|'.$invoice->control_code.'|'.$invoice->nit.'|'.$ice.'|0|0|'.$descuento;
$pdf->write2DBarcode($datosqr, 'QRCODE,L', '175', 
$pdf->GetY()-33, 25, 25, '', 'N');

//Close and output PDF document
$pdf->Output('factura.pdf', 'I');

//============================================================+
// END OF FILE (^_^)
//============================================================+
die;
?>


