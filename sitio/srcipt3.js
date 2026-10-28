// Pase el mouse y pare
// Ajustar un método en lugar de getElementById ()
function byId(id) {
    // Si es una ID de cadena, devuelve su objeto DOM
   return  typeof id==="string"?document.getElementById(id):id;
}

    var index=0,timer=null,banner=byId("banner").getElementsByTagName("div"),len=banner.length,dots=byId("dots").getElementsByTagName("span")
    ,prev=byId("prev"),next=byId("next");
// el número de tramo es igual a la imagen div

function slideImg() {
    // Obtenga el rango de deslizamiento del mouse
    var main=byId("main");
    // Desliza el temporizador y deja para continuar
    main.onmouseover=function () {
        if(timer){
            clearInterval(timer);
        }
    }
    main.onmouseout=function () {
        timer=setInterval(function () {
            index++;
            // len = 3, el índice solo puede ser 0, 1, 2
            if(index>=len){
                index=0;
            }
            // Cambia la imagen, también puedes hacer clic en el botón
            changeImg();
        },3000);// Llamado de vez en cuando, el lapso está activo cada tres segundos
    }
    // Se activa automáticamente en main
    main.onmouseout();

    // Haga clic en los puntos para cambiar la imagen e repita todos los eventos de clic de enlace de puntos
    for(var d=0;d<len;d++){
        // Agregue un atributo de ID a todos los tramos, el valor es d, como el índice del tramo actual
        dots[d].id=d;

        dots[d].onclick=function () {

            // Cambia el índice del tramo actual, pero hacer clic es el valor final de 3, porque la función cambia el alcance
            index=this.id;
            this.className="active";
            changeImg();
        }
    }

    // Siguiente, anterior
    next.onclick=function () {
        index++;
        if(index>=len){
            index=0;
        }
        changeImg();
    }

    prev.onclick=function () {
        index--;
        if(index<0){
            index=2;
        }
        changeImg();
    }

}

function changeImg() {
   // banner [index] .className = 'slide-active';
    // Iterar a través de img, dots, clear div y dots shadow
    // Ocultar todo primero, luego mostrar el actual
    for(var i=0;i<len;i++){
        banner[i].style.display='none';
        dots[i].className="";
    }
    banner[index].style.display='block';
    dots[index].className="active";
}


slideImg();