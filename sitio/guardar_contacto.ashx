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

        if (context.Request.HttpMethod != "POST")
        {
            context.Response.StatusCode = 405;
            context.Response.Write("{\"success\":false,\"error\":\"Método no permitido\"}");
            return;
        }

        try
        {
            // Leer JSON del cuerpo
            string body;
            using (var reader = new StreamReader(context.Request.InputStream))
            {
                body = reader.ReadToEnd();
            }

            var serializer = new JavaScriptSerializer();
            var datos = serializer.Deserialize<Dictionary<string, string>>(body);

            string nombre = (datos.ContainsKey("nombre") ? datos["nombre"] : "").Trim();
            string correo = (datos.ContainsKey("correo") ? datos["correo"] : "").Trim();
            string empresa = (datos.ContainsKey("empresa") ? datos["empresa"] : "").Trim();
            string interes = (datos.ContainsKey("interes") ? datos["interes"] : "").Trim();
            string comentario = (datos.ContainsKey("comentario") ? datos["comentario"] : "").Trim();

            // Validaciones
            if (string.IsNullOrEmpty(nombre) || string.IsNullOrEmpty(correo))
            {
                context.Response.StatusCode = 400;
                context.Response.Write("{\"success\":false,\"error\":\"El nombre y el correo son obligatorios\"}");
                return;
            }

            if (!correo.Contains("@") || !correo.Contains("."))
            {
                context.Response.StatusCode = 400;
                context.Response.Write("{\"success\":false,\"error\":\"Correo electrónico inválido\"}");
                return;
            }

            // Ruta del archivo CSV
            string ruta = context.Server.MapPath("~/contactos.csv");
            bool existe = File.Exists(ruta);

            // Escapar comillas y separadores para CSV
            Func<string, string> escapar = (s) =>
            {
                if (s == null) s = "";
                s = s.Replace("\"", "\"\"");
                return "\"" + s + "\"";
            };

            // Si no existe, crear cabecera
            if (!existe)
            {
                File.WriteAllText(ruta,
                    "Fecha,Nombre,Correo,Empresa,Interes,Comentario,IP\n",
                    System.Text.Encoding.UTF8);
            }

            // Agregar fila
            string fecha = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            string ip = context.Request.UserHostAddress;
            string linea = string.Join(",",
                escapar(fecha),
                escapar(nombre),
                escapar(correo),
                escapar(empresa),
                escapar(interes),
                escapar(comentario),
                escapar(ip)
            ) + "\n";

            File.AppendAllText(ruta, linea, System.Text.Encoding.UTF8);

            context.Response.Write("{\"success\":true,\"mensaje\":\"¡Gracias! Hemos recibido tu mensaje correctamente.\"}");
        }
        catch (Exception ex)
        {
            context.Response.StatusCode = 500;
            context.Response.Write("{\"success\":false,\"error\":\"Error al guardar: " +
                ex.Message.Replace("\"", "'") + "\"}");
        }
    }

    public bool IsReusable { get { return false; } }
}