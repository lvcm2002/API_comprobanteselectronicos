<style>
.footer .page-number:after { content: counter(page); }
@page {
    margin-bottom: 0.3in;
}
hr {
    display: block;
    height: 1px;
    border: 0;
    border-top: 3px solid #dfc;
    margin: 1em 0;
    padding: 0; 
    margin-top:1px;
    margin-bottom:2px;
}    
td.header {
    font-size: x-small;
    font-weight: bold;
    padding: 8px;
}
td.detail {
    font-size: small;
    padding: 8px;
}
table.detail {
    background-color:#fcfcfc;
    border-bottom: 1px solid #ddd;
}
table.detail tr:nth-child(even) {
    background-color: #eeeeee;
}
table.center {
    margin-left:auto; 
    margin-right:auto;
  }
  .large {
      font-size: x-large;
  }
  .larger {
      font-size: xx-large;
  }
</style>
<p>Factura Electr&oacute;nica N&deg; <?=$xml_array['NumeroConsecutivo']?>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fecha de Emisi&oacute;n: <?=$fechaEmision?><br> Ver. 4.2  
    <br>Clave Num&eacute;rica <?=$xml_array['Clave']?></p>
<hr>
<table>
<tbody>
<tr>
<td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>
<td>

<table>
<tbody>
<tr>
<td class="larger" style="text-align: center;"><strong><?=$emisorNombreComercial?></strong></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td class="large" style="text-align: center;"><?=$emisorNombre?></td>
</tr>
<tr>
    <td style="text-align: center;"><strong><?=$emisorIdentificacionTipo?>:</strong><br> <?=$emisorIdentificacionNumero?></td>
</tr>
</tbody>
</table>
</td>
<td><strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</strong></td>
<td>
<table style="height: 97px; float:right" width="281">
<tbody>
<tr>
<td style="width: 272px;"><strong>Tel&eacute;fono:</strong> +(<?=$xml_array['Emisor']['Telefono']['CodigoPais']?>)&nbsp;<?=$emisorTelefono?></td>
</tr>
<tr>
<td style="width: 272px;"><strong>Correo:</strong> <?=$xml_array['Emisor']['CorreoElectronico']?> </td>
</tr>
<tr>
<td style="width: 272px;">
<p><strong>Direcci&oacute;n:</strong> <?=$emisorUbicacionProvincia.', '.$emisorUbicacionCanton.', '.$emisorUbicacionDistrito?> <?=$emisorUbicacionOtrasSenas?></p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<hr style="margin-top:10px;"/>
<table style="height: 138px;" width="529">
<tbody>
<tr>
<td style="width: 351.2px;">
<table style="float: left;">
<tbody>
<tr>
<td><strong>Receptor:</strong><?=$xml_array['Receptor']['Nombre']?></td>
</tr>
<tr>
<td><strong><?=$receptorIdentificacionTipo?>:</strong> <strong><?=$receptorIdentificacionNumero?></strong></td>
</tr>
<tr>
<td><strong>Tel&eacute;fono</strong>:+(<?=$xml_array['Receptor']['Telefono']['CodigoPais']?>)&nbsp;<?=$receptorTelefono?></td>
</tr>
<tr>
<td><strong>Correo:</strong> <?=$xml_array['Receptor']['CorreoElectronico']?></td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td><strong>Direcci&oacute;n:</strong>&nbsp;  <?=$receptorUbicacionProvincia.', '.$receptorUbicacionCanton.', '.$receptorUbicacionDistrito?> <?=$receptorUbicacionOtrasSenas?></td>
</tr>
</tbody>
</table>
</td>
<td style="width: 363.2px;">
<table style="float: right;">
<tbody>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td><strong>Condici&oacute;n de Venta:</strong> Contado</td>
</tr>
<tr>
<td><strong>Medio de Pago:</strong> Efectivo</td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<p style="text-align: center;"><strong><br />Lineas de Detalle</strong></p>
<hr />
<table class="detail center">
