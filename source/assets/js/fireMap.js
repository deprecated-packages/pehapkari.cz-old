var map = null; // big map global var
var formMap = null; // small form map global var
var formMapMarker = null; // form marker find global var
var newUser = {}; // new user global var

/**
 * init firebase with default data
 */
firebase.initializeApp({
    apiKey: "AIzaSyC4AJjq7VHSSHpmOZtKoTcz33c7MzDdI2w", // i have no idea how hide this !!
    authDomain: "pehapkari.firebaseapp.com",
    databaseURL: "https://pehapkari.firebaseio.com",
    storageBucket: "pehapkari.appspot.com"
});

firebase.auth().signInAnonymously().catch();

/**
 * get setting for google map
 * @param integer zoom
 * @returns {{zoom: number, center: {lat: number, lng: number}, streetViewControl: boolean}}
 */
function crSettings(zoom) {
    return {
        zoom : zoom,
        center: {lat: 49.7437652, lng: 15.3364628},
        streetViewControl: false,
        scrollwheel:  false,
    }
}

/**
 * parent function loading maps after async load google maps api
 */
function initMaps() {


    if(document.getElementById("map")) {
        initMap();
    }

    if(document.getElementById("formMap")) {
        initFormMap();
    }

}

/**
 * init small google map for form to global variable to latest manipulation
 */
function initFormMap() {

    formMap = new google.maps.Map(document.getElementById('formMap'), crSettings(6));

}

/**
 * set info box for google marker
 * @param object user
 * @returns {string}
 */
function setInfoBox(user) {

    var controll = false;

    var pom = "<p>"
            + "<strong>" + user.name + " " + user.surname + " " + ((user.nickname !== undefined) ? "(" + user.nickname + ")" : "") + "</strong>"
            + "<br />"
            + "<a href='mailto:" + user.email + "'>" + user.email + "</a>"
            + "<br /><br />";

    if(user.web !== undefined) {
        pom += "<a href='" + user.web + "' target='_blank'>Webovky</a> | ";
        controll = true;
    }

    if(user.twitter !== undefined) {
        pom += "<a href='http://twitter.com/" + user.twitter + "' target='_blank'>Twitter</a> | ";
        controll = true;
    }

    if(user.github !== undefined) {
        pom += "<a href='http://github.com/" + user.github + "' target='_blank'>GitHub</a> | ";
        controll = true;
    }

    if(controll) {
        pom = pom.substr(0, pom.length - 3);
    }

    pom += "</p>";

    return pom;

}

/**
 * init big google map to global variable to latest manipulation
 */
function initMap() {

    //zoom set about width
    var z = (window.innerWidth < 500) ? 6 : 7 ;

    //init map
    map = new google.maps.Map(document.getElementById('map'), crSettings( z ));

    var info = new google.maps.InfoWindow();

    //fetch all data from firebase
    firebase.database().ref('users').once('value').then(function(snapshot) {

        // iterate result from firebase
        for(var i in snapshot.val() ) {

            // assign local user variable
            var user = snapshot.val()[i];

            //create google map marker
            var marker = new google.maps.Marker({
                position: {lat : user.lat, lng: user.lng},
                map: map
            });

            //set event listener to show info box after click on marker
            google.maps.event.addListener(marker, "click", (function(marker, user) {
                return function() {
                    info.close();
                    info.setContent(setInfoBox(user));
                    info.open(map, marker);
                }
            })(marker, user));

        }

    });
}

/**
 * change input style to success/error
 * @param dom input
 * @param boolean error
 */
function inputChanger(input, error) {
    if(error) {
        input.parentNode.className = "form-group has-error";
    } else {
        input.parentNode.className = "form-group has-success";
    }
}

/**
 * check if input not empty
 * set input value to global new user variable
 * @param dom input
 * @param boolean validate
 * @returns {boolean}
 */
function validateInput(input, validate) {
    if(input.value != null && input.value != "") {

        if(validate) {
            inputChanger(input,false);
        }

        newUser[input.name] = input.value;

        return true;

    } else {

        if(validate) {
            inputChanger(input, true);
        }

        return false;

    }
}

/**
 * search coordinate from google maps api
 */
function search() {

    //trigger city name
    var city = document.getElementById('city');

    //disabling input for long load
    city.disabled = true;

    //change button to loading icon for long load
    this.innerHTML = "<em class='fa fa-refresh fa-spin fa-fw'></em>";

    //when form map marker is exist remove it
    if(formMapMarker != null) {
        formMapMarker.setMap(null);
    }

    //ajax call to google maps api to get lat, lng values for city value
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "http://maps.google.com/maps/api/geocode/json?address=" + city.value);
    xhr.onload = function() {

        //when get response from google maps api
        if(xhr.status === 200) {

            //parse response to json for simple usage
            var response = JSON.parse(xhr.responseText);

            //check if json not empty
            if(response.results[0] != undefined) {

                //set lat, lng to global newUser variable
                newUser['lat'] = response.results[0].geometry.location.lat;
                newUser['lng'] = response.results[0].geometry.location.lng;

                //add new marker to small form map
                formMapMarker = new google.maps.Marker({
                    position: response.results[0].geometry.location,
                    map: formMap
                });

                //change adress input to sucess
                inputChanger(city.parentNode, false);

            } else {

                //change adress input to error status
                inputChanger(city.parentNode, true);

            }
        } else {

            //change adress input to error status
            inputChanger(city.parentNode, true);

        }
    };
    xhr.send();

    //revert text to find button
    this.innerHTML = 'Najdi!';

    //revert disabling input after load and execute data
    city.disabled = false;

}

if(document.getElementById("formMap")) {

    // work after load window
    window.onload = function() {

        //add listener to click on search button
        document.getElementById("search").addEventListener("click", search);

        //add listener to on blur from city input
        document.getElementById("city").addEventListener("blur", search);

        //add listener to click on submit form button
        document.getElementById("fire").addEventListener("click", function() {

            //validation all input from form
            //required
            var name = validateInput(document.getElementById("name"),true);
            var surname = validateInput(document.getElementById("surname"),true);
            var email = validateInput(document.getElementById("email"),true);

            //others
            var nickname = validateInput(document.getElementById("nickname"),false);
            var web = validateInput(document.getElementById("web"),false);
            var twitter = validateInput(document.getElementById("twitter"),false);
            var github = validateInput(document.getElementById("github"),false);

            //check if validation of required inputs is ok
            if(name && surname && email) {

                //check if is set new user lat and lng
                if(newUser.lat !== null && newUser.lng !== null) {

                    //fetch latest id from firebase
                    firebase.database().ref('users').orderByKey().limitToLast(1).once("value").then(function(snapshot) {

                        //foreach data from fireabse response
                        snapshot.forEach(function(data) {

                            //add new user to fireabse
                            var userId = parseInt(data.key) + 1 ;
                            firebase.database().ref('users/' + userId).set(newUser);

                        });

                    });


                    document.getElementById("formRem").innerHTML =
                        "<div class='col-sm-10 col-sm-offset-1 col-xs-12'>" +
                            "<div class='alert alert-success'>" +
                                "<strong>Výborně "+ newUser.name +"!</strong> Tvůj profil už je na mapě." +
                            "</div>" +
                        "</div>" +
                        "<div class='col-sm-10 col-sm-offset-1 col-xs-12 text-center'>" +
                            "<a href='/kde-najdes-php-kamose' class='btn btn-lg btn-info' role='button'>" +
                                "<em class='fa fa-arrow-left fa-fw'></em> Zpět na mapu" +
                            "</a>" +
                        "</div>";

                    //delete global new user data
                    delete newUser;

                } else {

                    //if data lat and lng not set set adress input to error
                    inputChanger(document.getElementById("city", true));

                }
            }

        });

    };

}