document.addEventListener('DOMContentLoaded', () => {
  const logoutLink = document.querySelector('.logout');

  if (logoutLink) {
      logoutLink.addEventListener('click', function (e) {
          e.preventDefault(); // Prevent immediate redirect

          Swal.fire({
              title: 'Are you sure?',
              text: 'You will be logged out of your session.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, logout',
              cancelButtonText: 'Cancel'
          }).then((result) => {
              if (result.isConfirmed) {
                  // Redirect to logout.php if confirmed
                  window.location.href = this.getAttribute('href');
              }
          });
      });
  }
});



