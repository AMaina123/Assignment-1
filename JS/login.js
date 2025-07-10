document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const message = document.getElementById("message");

  try {
    const res = await fetch("http://localhost:8080/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password })
    });

    if (res.ok) {
      const user = await res.json();
      localStorage.setItem("legalguide-user", JSON.stringify(user));
      window.location.href = "dashboard.html"; // change if needed
    } else {
      const err = await res.text();
      message.textContent = err || "Login failed.";
    }
  } catch (err) {
    message.textContent = "Connection error. Please try again.";
    console.error(err);
  }
});