document.addEventListener("DOMContentLoaded", () => {
  // 🎯 Load current user from localStorage (must be set after login)
  const user = JSON.parse(localStorage.getItem("legalguide-user"));
  const form = document.getElementById("profileForm");
  const message = document.getElementById("message");

  if (!user || !user.email) {
    message.textContent = "You must be logged in to view your profile.";
    return;
  }

  // 🔎 Fetch profile details from backend
  fetch(`http://localhost:8080/profile/${user.email}`)
    .then(res => {
      if (!res.ok) throw new Error("Could not load profile.");
      return res.json();
    })
    .then(data => {
      // 🧾 Pre-fill form inputs
      document.getElementById("name").value = data.name || "";
      document.getElementById("email").value = data.email || "";
      document.getElementById("phone").value = data.phone || "";
      document.getElementById("role").value = data.role || "";
    })
    .catch(err => {
      message.textContent = err.message;
      console.error("Profile fetch error:", err);
    });

  // ✏️ Form submission handler — PUTs updated profile
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    // 🧪 Extract updated values from form
    const updatedProfile = {
      name: document.getElementById("name").value.trim(),
      email: document.getElementById("email").value.trim(),  // read-only
      phone: document.getElementById("phone").value.trim(),
      role: document.getElementById("role").value.trim()     // read-only
    };

    // 🚀 Send update request to backend
    fetch("http://localhost:8080/profile/update", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updatedProfile)
    })
    .then(res => {
      if (!res.ok) throw new Error("Update failed.");
      message.style.color = "green";
      message.textContent = "Profile updated successfully.";
      localStorage.setItem("legalguide-user", JSON.stringify(updatedProfile));
    })
    .catch(err => {
      message.style.color = "crimson";
      message.textContent = err.message || "Could not update profile.";
      console.error("Update error:", err);
    });
  });
});