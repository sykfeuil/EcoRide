document.addEventListener('DOMContentLoaded', function() {

    const formDriver = document.getElementById('formDriver');
    const driverFormData = document.getElementById('driverFormData');

    showHideDriverData();

    formDriver.addEventListener('click', showHideDriverData);

});

function showHideDriverData() {
    if (formDriver.checked == true) {
        driverFormData.style.display = 'block';
    }
    else {
        driverFormData.style.display = 'none';
    }
}