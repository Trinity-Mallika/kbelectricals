var x = window.matchMedia("(max-width: 600px)");
myFunction(x);
x.addListener(myFunction)

function myFunction(x) {
    if (x.matches) { // If media query matches
        //alert('sdf');
        $(".main").css("margin-left", "0");
        $(".offcanvas").removeClass("show");
    } else {
        $(".main").css("margin-left", "230px");
        $(".offcanvas").addClass("show");

        const myOffcanvas = document.getElementById('staticBackdrop')
        myOffcanvas.addEventListener('hide.bs.offcanvas', event => {
            // do something...
            $(".main").css("margin-left", "0px");
        })
        myOffcanvas.addEventListener('show.bs.offcanvas', event => {
            // do something...
            $(".main").css("margin-left", "230px");
        })
    }
}

$(".nav .nav-item").find("a.active>span.down>i").removeClass("bi bi-chevron-right");
$(".nav .nav-item").find("a.active>span.down>i").addClass("bi bi-chevron-down");