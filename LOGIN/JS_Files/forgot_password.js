document.getElementById("forgotForm").addEventListener("submit", async (e) => {
    e.preventDefault();
  
    const formData = new FormData(e.target);
    const response = await fetch("../PHP_Files/forgot_password_process.php", {
      method: "POST",
      body: formData
    });
  
    const result = await response.json();
  
    if (result.success) {
      Swal.fire({
        title: "Reset Link Sent!",
        text: "Please check your email for password reset instructions.",
        icon: "success",
        confirmButtonColor: "#595959"
      });
    } else {
      Swal.fire({
        title: "Email Not Found",
        text: "No account found with that email address.",
        icon: "error",
        confirmButtonColor: "#595959"
      });
    }
  });
  