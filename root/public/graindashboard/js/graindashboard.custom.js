confirmLogout = function() {
    $('#signout-modal').modal('show');
    return false;
}

doLogout = function() {
    $('#signout-modal').modal('hide');
    window.location.href = 'home/logout';

    return false;
}