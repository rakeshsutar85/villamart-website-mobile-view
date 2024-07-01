
function showMobileSubMenu() {
    const mmv = document.getElementById("mobile-sub-menu");
    if (mmv.style.display === "block") {
        mmv.style.display = "none";
    }else {
        mmv.style.display = "block";
    }
}

function showMenu() {
    const mmv = document.getElementById("mobile-menu");
    mmv.style.display = "block";
    // const menuIcon = document.getElementById("menu-icon");
    // menuIcon.style.display = "none";
}

function hideMenu() {
    const mmv = document.getElementById("mobile-menu");
    mmv.style.display = "none";
    // const menuIcon = document.getElementById("menu-icon");
    // menuIcon.style.display = "block";
}

function openPopup() {
    document.getElementById('popup-overlay').style.display = 'block';
    // let vid = document.getElementById("myVideo");
    // vid.volume=0.2;
    document.getElementById("video-container").focus();

}

// Function to close the popup
function closePopup() {
    document.getElementById('popup-overlay').style.display = 'none';
    var video = document.getElementById("myVideo");
    video.pause(); // Pause the video
    video.currentTime = 0;
}

// Open the popup when the page loads
window.onload = function () {
    openPopup();

    var textcolor = document.getElementById('about');
    textcolor.textcolor = "red";
};


// Suppored by Start


// Supported by End


// Achivement Start

function animate(obj, initVal, lastVal, duration) {
    let startTime = null;

    //get the current timestamp and assign it to the currentTime variable
    let currentTime = Date.now();

    //pass the current timestamp to the step function
    const step = (currentTime) => {

        //if the start time is null, assign the current time to startTime
        if (!startTime) {
            startTime = currentTime;
        }

        //calculate the value to be used in calculating the number to be displayed
        const progress = Math.min((currentTime - startTime) / duration, 1);

        //calculate what to be displayed using the value gotten above
        obj.innerHTML = Math.floor(progress * (lastVal - initVal) + initVal);

        //checking to make sure the counter does not exceed the last value (lastVal)
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            window.cancelAnimationFrame(window.requestAnimationFrame(step));
        }
    };
    //start animating
    window.requestAnimationFrame(step);
}
// let text1 = document.getElementById('0101');
// let text2 = document.getElementById('0102');
// let text3 = document.getElementById('0103');
// const load = () => {
//     animate(text1, 0, 511, 7000);
//     animate(text2, 0, 232, 7000);
//     animate(text3, 100, 25, 7000);
// }


//Achivement End

function videoOutsideClose(){
    window.addEventListener('click', function (e) {

        if (document.getElementById("popup-content").contains(e.target)) {
            //alert("Please click outside the video");
            
        } else {
            closePopup();
            //alert("Clicked outside Box");
        }
    })
}


function validateForm() {
    var x = document.forms["contactForm"]["fullname"].value;

    var result = validateName(x);

    if (result !== "Valid name.") {
        document.getElementById("error").innerHTML = result;
        return false;
    }

    x = document.forms["contactForm"]["email"].value;

    result = validateEmail(x);
    if (result !== "Valid email.") {
        document.getElementById("error").innerHTML = result;
        return false;
    }

    x = document.forms["contactForm"]["address"].value;

    result = validateAddress(x);
   if (result !== "Valid Address.") {
        document.getElementById("error").innerHTML = result;
        return false;
    }

    x = document.forms["contactForm"]["mobile"].value;

    result = validateMobileNumber(x);
   if (result !== "Valid mobile.") {
        document.getElementById("error").innerHTML = result;
        return false;
    }

    x = document.forms["contactForm"]["message"].value;

    result = validateMessage(x);
   if (result !== "Valid Message.") {
        document.getElementById("error").innerHTML = result;
        return false;
    }
}


function validateName(name) {
    if (name === "") {
        return "Name cannot be empty.";
    }

    var regex = /^[a-zA-Z]+$/;
    if (!regex.test(name)) {
        return "Name can only contain letters.";
    }

    if (name.length > 25) {
        return "Name cannot be longer than 25 characters.";
    }

    return "Valid name.";
}

function validateEmail(email) {
    
    if (email === "") {
        return "Email cannot be empty.";
    }

    var regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;;
    if (!regex.test(email)) {
        return "Not a valid email.";
    }


    return "Valid email.";

}

function validateAddress(address) {
    if (address === "") {
      return "Address cannot be empty.";
    }
  

    var regex = /^[a-zA-Z0-9\s,'-]*$/;
    if (!regex.test(address)) {
      return "Address can only contain letters, numbers, and special characters.";
    }
  
    return "Valid Address.";
  }


  function validateMobileNumber(mobileNumber) {
    // Regular expression to check valid mobile number
    const regex = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
  
    // If mobile number is empty return false
    if (mobileNumber == null) {
      return "Mobile number cannot be empty.";
    }
  
    // Return true if the mobile number matched the regex
    if (!regex.test(mobileNumber)) {
      return "Invalid mobile number";
    }
    
    
    return "Valid mobile.";
    
  }

  function validateMessage(address) {
    if (message === "") {
      return "Message cannot be empty.";
    }
  

    var regex = /^[a-zA-Z0-9\s,'-]*$/;
    if (!regex.test(address)) {
      return "Message can only contain letters, numbers, and special characters.";
    }
  
    return "Valid Message.";
  }