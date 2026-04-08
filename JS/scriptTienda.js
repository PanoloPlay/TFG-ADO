var json_datos;

$(window).on('load', function() {

    const action = "cargarDatos";
    doSQLQuery(action, null);
    //testSQLQuery(action, null);
});

function doSQLQuery(action, obj) {

    $.ajax({

        url: "../AJAX/ajaxData.php",
        type: "POST",
        async: true,

        data:{

            action: action,
            obj: obj
        },

        success: function(response) {

            if (response == "Connection Failed!") {

                json_datos = "La conexión a la base de datos ha fallado";
            }
            else if (response == "No data found!" || response == "Query Failed!") {

                json_datos = "No hay registros para mostrar";
            }
            else {

                json_datos = JSON.parse(response);
            }
            console.log(json_datos);
        },

        error: function(xhr, status, error) {

            console.log("Error al cargar el archivo JSON:", error);
        }
    });
}

function testSQLQuery(action, obj) {

    $.ajax({

        url: "./ajaxData.php",
        type: "POST",
        async: true,

        data:{

			action:action,
            obj: obj
		},

        success: function(response) {

            console.log(response);
        },
        error: function(xhr, status, error) {
            console.log("Error al cargar el archivo JSON:", error);
        }
    });
}