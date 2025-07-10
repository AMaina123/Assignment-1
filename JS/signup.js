document.getElementById("signupForm").addEventListener("submit", function (e) {
  e.preventDefault();

  // Capture form field values
  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const role = document.getElementById("role").value;
  const phone = document.getElementById("phone").value.trim();

  // Pack into user object
  const userData = {
    name,
    email,
    password,
    role,
    phone
  };

  // Send POST request to backend
  fetch("http://localhost:8080/signup", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(userData)
  })
    .then(response => {
      if (!response.ok) {
        return response.text().then(msg => {
          throw new Error(msg || "Signup failed.");
        });
      }
      return response.json();
    })
    .then(data => {
      alert("✅ Signup successful! Welcome, " + data.name);
      // Redirect or reset form if needed
      document.getElementById("signupForm").reset();
    })
    .catch(error => {
      alert("🚫 Signup error: " + error.message);
      console.error("Signup error:", error);
    });
});