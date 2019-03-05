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

/**
 * get setting for google map
 * @param integer zoom
 * @returns {{zoom: number, center: {lat: number, lng: number}, streetViewControl: boolean}}
 */
function crSettings(zoom) {
    return {
        zoom : zoom,
        center: {lat: 49.817, lng: 15.472},
        streetViewControl: false,
    }
}

/**
 * parent function loading maps after async load google maps api
 */
function initMaps() {
    initMap();
    initFormMap();
}

/**
 * init small google map for form to global variable to latest manipulation
 */
function initFormMap() {

    formMap = new google.maps.Map(document.getElementById('formMap'), crSettings(6));

}

/**
 * init big google map to global variable to latest manipulation
 */
function initMap() {

    //init map
    map = new google.maps.Map(document.getElementById('map'), crSettings(7));

    //fetch all data from firebase
    firebase.database().ref('users').once('value').then(function(snapshot) {

        // iterate result from firebase
        for(var i in snapshot.val() ) {

            // assign local user variable
            var user = snapshot.val()[i];

            //create google map marker
            var marker = new google.maps.Marker({
                position: {lat : user.lat, lng: user.lng},
                map: map,
            });

            //check user phone empty
            var tel = "";

            if(user.tel !== undefined) {
                tel += "<br /><a href='tel:"+user.tel+"' title='"+user.tel+"'>"+user.tel+"</a>"
            }

            // set google marker info with full name, mail, maybe phone
            var info = new google.maps.InfoWindow({
                content:
                "<p>" +
                "<strong>"+user.name+" "+user.surname+"</strong>" +
                "<br />" +
                "<a href='mailto:"+user.email+"' title='"+user.email+"'>"+user.email+"</a>" +
                tel +
                "</p>"
            });

            //set event listener to show info box after click on marker
            google.maps.event.addListener(marker, "click", function(e) {
                info.open(map, this);
            });

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

// work after load window
window.onload = function() {

    //add listener to click on search button
    document.getElementById("search").addEventListener("click", function() {

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
        this.innerText = 'Najdi!';

        //revert disabling input after load and execute data
        city.disabled = false;

    });

    //add listener to click on submit form button
    document.getElementById("fire").addEventListener("click", function() {

        //validation all input from form
        var name = validateInput(document.getElementById("name"),true);
        var surname = validateInput(document.getElementById("surname"),true);
        var email = validateInput(document.getElementById("email"),true);
        var tel = validateInput(document.getElementById("tel"),false);

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

                //add new marker to big map from actual data
                var marker = new google.maps.Marker({
                    position: {lat: newUser.lat, lng: newUser.lng},
                    map: map
                });

                //add info box to new marker from actual data
                var info = new google.maps.InfoWindow({
                    content:
                    "<p>" +
                    "<strong>"+newUser.name+" "+newUser.surname+"</strong>" +
                    "<br />" +
                    "<a href='mailto:"+newUser.email+"' title='"+newUser.email+"'>"+newUser.email+"</a>" +
                    "</p>"
                });

                //add listener to show new marker info box
                google.maps.event.addListener(marker, "click", function(e) {
                    info.open(map, this);
                });

                //replace form with map for succes alert message
                document.getElementById("formRem").innerHTML =
                    "<div class='alert alert-success text-center' role='alert'>" +
                    "<span><strong>Paráda!</strong> Už jsi mezi námi</span>" +
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