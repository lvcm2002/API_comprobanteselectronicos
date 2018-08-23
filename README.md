# apiproxy
proxy FacturaElectronica

###para llamar al servicio desde el browser mandar los siguientes parametros
* r = // este es el servicio
* data = // este es el json con los datos de la factura

servicios (r=) :
* enums: trae los enums de los campos y algunas caracterisitcas como regex si se desean usar
* struct:  trae una estructura json vacia del documento
* example: trae una estructura json de ejemplo para guiarse
* save:    guarda en el servidor el archivo json
* calcula: calcula los totales y subtotales de la factura
* genXML:  convierte json a xml
* saveXML:  guarda en el servidor el archivo xml
* validate: valida json contra xsd

ejemplo:
http://localhost/factura.php?r=example&data={...}

###los campos que el servicio calcula son:
* Clave
* NumeroConsecutivo
* MontoTotal
* SubTotal
* Impuesto.Monto
* ResumenFactura.TotalMercanciasGravadas
* ResumenFactura.TotalServGravados
* ResumenFactura.TotalMercanciasExentas
* ResumenFactura.TotalServExentos
* ResumenFactura.TotalGravado
* ResumenFactura.TotalExento
* ResumenFactura.TotalVenta
* ResumenFactura.TotalDescuentos
* ResumenFactura.TotalImpuesto
* ResumenFactura.TotalComprobante
* Normativa.NumeroResolucion
* Normativa.FechaResolucion

