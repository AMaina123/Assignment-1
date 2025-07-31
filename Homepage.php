<?php
session_start();

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: Homepage.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Home </title>
     <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div>
        <div class="topnav">
        <a href="Homepage.php"> Home </a> 
        <a href="Dashboard.php"> Dashboard</a> 
        <a href="ContactUs.php"> Contact Us</a>
        <div class="topnav-right">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="Profile.php">My Profile</a>
            <a href="Homepage.php?logout=true" style="color: #dc3545;">Logout</a>
            <?php else: ?>
                <a href="SignUp.php">Sign Up</a>
                <a href="Login.php">Login</a>
                <?php endif; ?>
</div>
    </div>
    </div>

    <div class="header">
    <h1> Welcome To The LegalGuide Website!</h1>
    </div>
    
    <div class="container">
        <div class="main-content">
            <p> Welcome to LegalGuide, your trusted AI-powered legal companion. We simplify complex legal matters into clear, accessible insights, empowering you to make informed decisions with confidence. Whether you're navigating contracts, understanding your rights, or exploring legal frameworks, our user-friendly approach ensures clarity and convenience. Start your journey to legal empowerment today! </p>
           
            
            <div class="second-content"> 
                <p><img src="Images/lawyer.png" alt="description" style="float: left; margin-right: 15px;" width="200">
                <p>LegalGuide offers a structured, AI-powered platform that simplifies the process of accessing legal assistance, especially for individuals who lack the resources or expertise to navigate complex legal systems. At its core, the system allows users to submit legal queries in natural language, which are then processed using natural language processing (NLP) and domain-trained AI models. The responses are presented in simplified, context-aware language, referencing relevant legal documents such as statutes, case law, or procedural guidelines. This empowers users with clear, actionable legal guidance without the need for expensive consultations or extensive legal training.</p>
                
                <p>Beyond automated legal advice, LegalGuide integrates features such as secure authentication protocols, role-based access control, and a consultation scheduling module that connects users to real human lawyers when needed. The platform maintains a structured legal knowledge base, enabling users to retrieve previous queries, track response history, and engage with tailored legal content. Lawyers can log in to validate complex inquiries, contribute expert advice, and manage scheduled appointments, ensuring both scalability and accuracy in service delivery. Altogether, LegalGuide positions itself as a hybrid legal assistant—blending accessibility, automation, and human expertise to address everyday legal challenges affordably and effectively. </p>

         </div>
         </div>
         <div class="sidebar">
            <a href="Dashboard.php"> Dashboard</a> <br> 
            <a href="Profile.php"> My Profile</a> <br>
            <a href="ContactUs.php"> Contact Us</a> <br>
    </div>    
    
</div>
    
    <div class="footer">
         <p>&copy; 2025 LegalGuide. All rights reserved.</p>
         <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
    </div>

    
</body>
</html>