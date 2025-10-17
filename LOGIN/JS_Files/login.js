document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);

        const response = await fetch("../PHP_Files/login_process.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                title: "Login Successful!",
                text: `Welcome back, ${result.role}.`,
                icon: "success",
                confirmButtonColor: "#595959",
            }).then(() => window.location.href = "../HTML_Files/login.html");
        } else {
            Swal.fire({
                title: "Login Failed",
                text: "Invalid email or password.",
                icon: "error",
                confirmButtonColor: "#595959",
            });
        }
    });
});


  document.addEventListener("DOMContentLoaded", () => {
    const emailInput = document.getElementById("email");
    const rememberMe = document.getElementById("rememberMe");
  
    // Load saved email on page load
    const savedEmail = localStorage.getItem("rememberedEmail");
    if (savedEmail) {
      emailInput.value = savedEmail;
      rememberMe.checked = true;
    }
  
    // Save or remove email based on checkbox
    rememberMe.addEventListener("change", () => {
      if (rememberMe.checked) {
        localStorage.setItem("rememberedEmail", emailInput.value);
      } else {
        localStorage.removeItem("rememberedEmail");
      }
    });
  });
  
  