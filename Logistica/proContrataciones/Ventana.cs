using System;
using System.Web.UI;

namespace proCont
{
	/// <summary>
	/// Descripción breve de Ventana.
	/// </summary>
	public class Ventana
	{
		public string JavaScript;

		public Ventana()
		{JavaScript="JavaScript";}


		public void AbrirVentanaHija(Page pagina,string url,string param) 
		{ 
			
			string cadena_script=""; 
			
			
			cadena_script ="<script language='"+JavaScript+"'>"+" window.open("+"'"+url+"'"+", 'CustomPopUp'," +"'"+param+"'"+")</script>"; 
			pagina.RegisterStartupScript("Popup",cadena_script); 
			
			
		} 

		public void ActualizarPadre(Page pagina,string url) 
		{ 
			string cadena_script=""; 
			cadena_script = "<script language='"+JavaScript+"'>" + "var pWin;" + 
				"function setParent(){pWin=top.window.opener;}"+ 
				"function reloadParent(){pWin.location.href="+"'"+url+"'"+";}"+ 
				"setParent();"+ 
				"reloadParent();"+ 
				"</script>"; 
			pagina.RegisterClientScriptBlock("VentanaHija",cadena_script); 
		} 

		public void CerrarVentana(System.Web.UI.Page pagina) 
		{ 
			string cadena_script=""; 
			cadena_script = "<script language='"+JavaScript+"'>" + 
				"function cerrar(){"+ 
				"var ventana=window.self;"+ 
				"ventana.opener=window.self;"+ 
				"ventana.close();}"+ 
				"cerrar();"+ 
				"</script>"; 
			pagina.RegisterClientScriptBlock("CerrarVentana",cadena_script); 

		}
		
	}
}
