<%@ WebHandler Language="C#" Class="GuardarContacto" %>

using System;
using System.IO;
using System.Web;
using System.Web.Script.Serialization;
using System.Collections.Generic;

public class GuardarContacto : IHttpHandler
{
    public void ProcessRequest(HttpContext context)
    {
        context.Response.ContentType = "application/json";
        context.Response.ContentEncoding = System.Text.Encoding.UTF8;

        try
        {
            if (context.Request.HttpMethod != "POST")
            {
                EscribirJSON(context, false, "Método no permitido", 405);
                return;
            }

            // Leer JSON del cuerpo
            string body;
            using (var reader = new StreamReader(context.Request.InputStream))
            {
                body = reader.ReadToEnd();
            }

            Dictionary<string, string> datos;
            try
            {
                var serializer = new JavaScriptSerializer();
                datos = serializer.Deserialize<Dictionary<string, string>>(body);
            }
            catch
            {
                EscribirJSON(context, false, "Los datos enviados no son válidos", 400);
                return;
            }

            if (datos == null)
            {
                EscribirJSON(context, false, "No se recibieron datos", 400);
                return;
            }

            string nombre     = (datos.ContainsKey("nombre")     ? datos["nombre"]     : "").Trim();
            string correo     = (datos.ContainsKey("correo")     ? datos["correo"]     : "").Trim();
            string empresa    = (datos.ContainsKey("empresa")    ? datos["empresa"]    : "").Trim();
            string interes    = (datos.ContainsKey("interes")    ? datos["interes"]    : "").Trim();
            string comentario = (datos.ContainsKey("comentario") ? datos["comentario"] : "").Trim();

            // Validaciones
            if (string.IsNullOrEmpty(nombre) || string.IsNullOrEmpty(correo))
            {
                EscribirJSON(context, false, "El nombre y el correo son obligatorios", 400);
                return;
            }

            if (correo.IndexOf("@") < 0 || correo.IndexOf(".") < 0)
            {
                EscribirJSON(context, false, "Correo electrónico inválido", 400);
                return;
            }

            // Ruta del archivo CSV (en la raíz del sitio)
            string ruta = context.Server.MapPath("~/contactos.csv");

            // Escritura del archivo con manejo de errores detallado
            try
            {
                bool existe = File.Exists(ruta);

                Func<string, string> escapar = (s) =>
                {
                    if (s == null) s = "";
                    s = s.Replace("\"", "\"\"");
                    return "\"" + s + "\"";
                };

                if (!existe)
                {
                    File.WriteAllText(ruta,
                        "Fecha,Nombre,Correo,Empresa,Interes,Comentario,IP\r\n",
                        System.Text.Encoding.UTF8);
                }

                string fecha = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                string ip    = context.Request.UserHostAddress;
                string linea = string.Join(",",
                    escapar(fecha),
                    escapar(nombre),
                    escapar(correo),
                    escapar(empresa),
                    escapar(interes),
                    escapar(comentario),
                    escapar(ip)
                ) + "\r\n";

                File.AppendAllText(ruta, linea, System.Text.Encoding.UTF8);
            }
            catch (UnauthorizedAccessException)
            {
                EscribirJSON(context, false,
                    "Permiso denegado. El usuario IIS_IUSRS necesita permiso de escritura en la carpeta raiz del sitio.", 500);
                return;
            }
            catch (Exception exArchivo)
            {
                EscribirJSON(context, false,
                    "No se pudo escribir el archivo CSV. Detalle: " + Sanitizar(exArchivo.Message), 500);
                return;
            }

            EscribirJSON(context, true,
                "¡Gracias! Hemos recibido tu mensaje correctamente.", 200);
        }
        catch (Exception ex)
        {
            EscribirJSON(context, false,
                "Error interno: " + Sanitizar(ex.Message), 500);
        }
    }

    // Elimina caracteres que puedan romper el JSON
    private string Sanitizar(string texto)
    {
        if (string.IsNullOrEmpty(texto)) return "";
        // Reemplaza backslash y comillas por caracteres seguros
        return texto.Replace("\\", "/").Replace("\"", "'").Replace("\r", " ").Replace("\n", " ");
    }

    // Escribe una respuesta JSON correctamente escapada
    private void EscribirJSON(HttpContext context, bool exito, string mensaje, int statusCode)
    {
        context.Response.StatusCode = statusCode;
        var serializer = new JavaScriptSerializer();
        var obj = new Dictionary<string, object>();
        if (exito)
        {
            obj["success"] = true;
            obj["mensaje"] = mensaje;
        }
        else
        {
            obj["success"] = false;
            obj["error"] = mensaje;
        }
        context.Response.Write(serializer.Serialize(obj));
    }

    public bool IsReusable { get { return false; } }
}