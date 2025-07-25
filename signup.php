<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
       
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // Validate inputs
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            throw new Exception("All required fields must be filled.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }
        
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match.");
        }
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already registered.");
        }
        $stmt->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = $_POST['role'] ?? 'user'; // Default role
        
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $hashedPassword, $role);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating user: " . $stmt->error);
            }
            
            $user_id = $conn->insert_id;
            $stmt->close();
            
            // Insert profile
            $fullName = $firstName . ' ' . $lastName;
            $age = $_POST['age'] ?? null;
            $location = $_POST['location'] ?? null;
            $stemField = $_POST['stemField'] ?? null;
            $interests = $_POST['interests'] ?? null;
            $skills = $_POST['skills'] ?? null;
            $goals = $_POST['goals'] ?? null;
            $communicationStyle = $_POST['communicationTone'] ?? null;
            $personalityTraits = $_POST['personalityType'] ?? null;
            $availability = $_POST['availability'] ?? null;
            
            $stmt2 = $conn->prepare("INSERT INTO profiles 
                (user_id, full_name, age, location, stem_field, interests, skills, goals, communication_style, personality_traits, availability)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
            $stmt2->bind_param("issssssssss", 
                $user_id, $fullName, $age, $location, $stemField, $interests, 
                $skills, $goals, $communicationStyle, $personalityTraits, $availability);
                
            if (!$stmt2->execute()) {
                throw new Exception("Error creating profile: " . $stmt2->error);
            }
            $stmt2->close();
            
            $conn->commit();
            
            $_SESSION['user_id'] = $user_id;
            
            header("Location: profile.php");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        $_SESSION['signup_error'] = $e->getMessage();
        header("Location: signup.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - HerMatchUp</title>
    <link rel="stylesheet" href="css/signupCss.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo" onclick="window.location.href='index.php'">
                <i class="fas fa-handshake"></i>
                <span>HerMatchUp</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php" class="nav-link">Login</a>
            </div>
        </div>
    </nav>

    <!-- Sign Up Section -->
    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Join HerMatchUp</h1>
                    <p>Start your mentorship journey today</p>
                </div>
                
                
<form class="auth-form" id="signup-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="lastName" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                        <small>Must be at least 8 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>

                    <div class="form-group">
                        <label for="stemField">Primary STEM Field *</label>
                        <select id="stemField" name="stemField" required>
                            <option value="">Select your field</option>
                            <option value="aerospace-engineering">Aerospace Engineering</option>
                            <option value="agricultural-science">Agricultural Science</option>
                            <option value="anatomy">Anatomy</option>
                            <option value="anthropology">Anthropology</option>
                            <option value="applied-mathematics">Applied Mathematics</option>
                            <option value="archaeology">Archaeology</option>
                            <option value="architecture">Architecture</option>
                            <option value="artificial-intelligence">Artificial Intelligence</option>
                            <option value="astronomy">Astronomy</option>
                            <option value="astrophysics">Astrophysics</option>
                            <option value="biochemistry">Biochemistry</option>
                            <option value="bioengineering">Bioengineering</option>
                            <option value="bioinformatics">Bioinformatics</option>
                            <option value="biology">Biology</option>
                            <option value="biomedical-engineering">Biomedical Engineering</option>
                            <option value="biophysics">Biophysics</option>
                            <option value="biotechnology">Biotechnology</option>
                            <option value="botany">Botany</option>
                            <option value="chemical-engineering">Chemical Engineering</option>
                            <option value="chemistry">Chemistry</option>
                            <option value="civil-engineering">Civil Engineering</option>
                            <option value="climate-science">Climate Science</option>
                            <option value="computer-engineering">Computer Engineering</option>
                            <option value="computer-science">Computer Science</option>
                            <option value="cybersecurity">Cybersecurity</option>
                            <option value="data-science">Data Science</option>
                            <option value="earth-science">Earth Science</option>
                            <option value="ecology">Ecology</option>
                            <option value="electrical-engineering">Electrical Engineering</option>
                            <option value="electronics">Electronics</option>
                            <option value="environmental-engineering">Environmental Engineering</option>
                            <option value="environmental-science">Environmental Science</option>
                            <option value="epidemiology">Epidemiology</option>
                            <option value="food-science">Food Science</option>
                            <option value="forensic-science">Forensic Science</option>
                            <option value="genetics">Genetics</option>
                            <option value="geography">Geography</option>
                            <option value="geology">Geology</option>
                            <option value="geophysics">Geophysics</option>
                            <option value="industrial-engineering">Industrial Engineering</option>
                            <option value="information-technology">Information Technology</option>
                            <option value="machine-learning">Machine Learning</option>
                            <option value="marine-biology">Marine Biology</option>
                            <option value="materials-science">Materials Science</option>
                            <option value="mathematics">Mathematics</option>
                            <option value="mechanical-engineering">Mechanical Engineering</option>
                            <option value="medical-research">Medical Research</option>
                            <option value="meteorology">Meteorology</option>
                            <option value="microbiology">Microbiology</option>
                            <option value="nanotechnology">Nanotechnology</option>
                            <option value="neuroscience">Neuroscience</option>
                            <option value="nuclear-engineering">Nuclear Engineering</option>
                            <option value="oceanography">Oceanography</option>
                            <option value="paleontology">Paleontology</option>
                            <option value="pharmacology">Pharmacology</option>
                            <option value="physics">Physics</option>
                            <option value="physiology">Physiology</option>
                            <option value="planetary-science">Planetary Science</option>
                            <option value="psychology">Psychology</option>
                            <option value="public-health">Public Health</option>
                            <option value="quantum-computing">Quantum Computing</option>
                            <option value="robotics">Robotics</option>
                            <option value="software-engineering">Software Engineering</option>
                            <option value="statistics">Statistics</option>
                            <option value="systems-engineering">Systems Engineering</option>
                            <option value="telecommunications">Telecommunications</option>
                            <option value="veterinary-science">Veterinary Science</option>
                            <option value="zoology">Zoology</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="experienceLevel">Experience Level *</label>
                        <select id="experienceLevel" name="experienceLevel" required>
                            <option value="">Select your experience level</option>
                            <option value="0-1-years">0-1 Years</option>
                            <option value="1-2-years">1-2 Years</option>
                            <option value="2-3-years">2-3 Years</option>
                            <option value="3-5-years">3-5 Years</option>
                            <option value="5-plus-years">5+ Years</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="signupPersonalityType">Personality Type (MBTI)</label>
                        <select id="signupPersonalityType" name="personalityType">
                            <option value="">Select your MBTI type</option>
                            <option value="INTJ">INTJ - The Architect</option>
                            <option value="INTP">INTP - The Logician</option>
                            <option value="ENTJ">ENTJ - The Commander</option>
                            <option value="ENTP">ENTP - The Debater</option>
                            <option value="INFJ">INFJ - The Advocate</option>
                            <option value="INFP">INFP - The Mediator</option>
                            <option value="ENFJ">ENFJ - The Protagonist</option>
                            <option value="ENFP">ENFP - The Campaigner</option>
                            <option value="ISTJ">ISTJ - The Logistician</option>
                            <option value="ISFJ">ISFJ - The Defender</option>
                            <option value="ESTJ">ESTJ - The Executive</option>
                            <option value="ESFJ">ESFJ - The Consul</option>
                            <option value="ISTP">ISTP - The Virtuoso</option>
                            <option value="ISFP">ISFP - The Adventurer</option>
                            <option value="ESTP">ESTP - The Entrepreneur</option>
                            <option value="ESFP">ESFP - The Entertainer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="signupCommunicationTone">Preferred Communication Tone</label>
                        <select id="signupCommunicationTone" name="communicationTone">
                            <option value="">Select communication tone</option>
                            <option value="Friendly and Casual">Friendly & Casual</option>
                            <option value="Professional and Formal">Professional & Formal</option>
                            <option value="Supportive and Empathetic">Supportive & Empathetic</option>
                            <option value="Direct and Straightforward">Direct & Straightforward</option>
                            <option value="Analytical and Detailed">Analytical & Detailed</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="signupResponseTime">Expected Response Time</label>
                        <select id="signupResponseTime" name="responseTime">
                            <option value="">Select expected response time</option>
                            <option value="Within 24 hours">Within 24 hours</option>
                            <option value="Within 48 hours">Within 48 hours</option>
                            <option value="A few days">A few days</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="signupCommunicationFrequency">Communication Frequency</label>
                        <select id="signupCommunicationFrequency" name="communicationFrequency">
                            <option value="">Select communication frequency</option>
                            <option value="Any Frequency">Any Frequency</option>
                            <option value="Frequent Check-ins">Frequent Check-ins</option>
                            <option value="Occasional Messages">Occasional Messages</option>
                            <option value="As-Needed Communication">As-Needed Communication</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="signupLocation">Country</label>
                        <select id="signupLocation" name="location">
                            <option value="">Select your country</option>
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Australia">Australia</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Japan">Japan</option>
                            <option value="India">India</option>
                            <option value="Brazil">Brazil</option>
                            <option value="China">China</option>
                            <option value="Kosovo">Kosovo</option>
                            <option value="Albania">Albania</option>
                            
                            <option value="Afghanistan">Afghanistan</option>
                            <option value="Algeria">Algeria</option>
                            <option value="Andorra">Andorra</option>
                            <option value="Angola">Angola</option>
                            <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Armenia">Armenia</option>
                            <option value="Austria">Austria</option>
                            <option value="Azerbaijan">Azerbaijan</option>
                            <option value="Bahamas">Bahamas</option>
                            <option value="Bahrain">Bahrain</option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Barbados">Barbados</option>
                            <option value="Belarus">Belarus</option>
                            <option value="Belgium">Belgium</option>
                            <option value="Belize">Belize</option>
                            <option value="Benin">Benin</option>
                            <option value="Bhutan">Bhutan</option>
                            <option value="Bolivia">Bolivia</option>
                            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                            <option value="Botswana">Botswana</option>
                            <option value="Brunei">Brunei</option>
                            <option value="Bulgaria">Bulgaria</option>
                            <option value="Burkina Faso">Burkina Faso</option>
                            <option value="Burundi">Burundi</option>
                            <option value="Cabo Verde">Cabo Verde</option>
                            <option value="Cambodia">Cambodia</option>
                            <option value="Cameroon">Cameroon</option>
                            <option value="Central African Republic">Central African Republic</option>
                            <option value="Chad">Chad</option>
                            <option value="Chile">Chile</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Comoros">Comoros</option>
                            <option value="Congo, Dem. Rep.">Congo, Dem. Rep.</option>
                            <option value="Congo, Rep.">Congo, Rep.</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                            <option value="Croatia">Croatia</option>
                            <option value="Cuba">Cuba</option>
                            <option value="Cyprus">Cyprus</option>
                            <option value="Czechia">Czechia</option>
                            <option value="Denmark">Denmark</option>
                            <option value="Djibouti">Djibouti</option>
                            <option value="Dominica">Dominica</option>
                            <option value="Dominican Republic">Dominican Republic</option>
                            <option value="East Timor">East Timor</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="Egypt">Egypt</option>
                            <option value="El Salvador">El Salvador</option>
                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                            <option value="Eritrea">Eritrea</option>
                            <option value="Estonia">Estonia</option>
                            <option value="Eswatini">Eswatini</option>
                            <option value="Ethiopia">Ethiopia</option>
                            <option value="Fiji">Fiji</option>
                            <option value="Finland">Finland</option>
                            <option value="Gabon">Gabon</option>
                            <option value="Gambia">Gambia</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Ghana">Ghana</option>
                            <option value="Greece">Greece</option>
                            <option value="Grenada">Grenada</option>
                            <option value="Guatemala">Guatemala</option>
                            <option value="Guinea">Guinea</option>
                            <option value="Guinea-Bissau">Guinea-Bissau</option>
                            <option value="Guyana">Guyana</option>
                            <option value="Haiti">Haiti</option>
                            <option value="Honduras">Honduras</option>
                            <option value="Hungary">Hungary</option>
                            <option value="Iceland">Iceland</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Iran">Iran</option>
                            <option value="Iraq">Iraq</option>
                            <option value="Ireland">Ireland</option>
                            <option value="Israel">Israel</option>
                            <option value="Italy">Italy</option>
                            <option value="Jamaica">Jamaica</option>
                            <option value="Jordan">Jordan</option>
                            <option value="Kazakhstan">Kazakhstan</option>
                            <option value="Kenya">Kenya</option>
                            <option value="Kiribati">Kiribati</option>
                            <option value="Kuwait">Kuwait</option>
                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                            <option value="Laos">Laos</option>
                            <option value="Latvia">Latvia</option>
                            <option value="Lebanon">Lebanon</option>
                            <option value="Lesotho">Lesotho</option>
                            <option value="Liberia">Liberia</option>
                            <option value="Libya">Libya</option>
                            <option value="Liechtenstein">Liechtenstein</option>
                            <option value="Lithuania">Lithuania</option>
                            <option value="Luxembourg">Luxembourg</option>
                            <option value="Madagascar">Madagascar</option>
                            <option value="Malawi">Malawi</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Maldives">Maldives</option>
                            <option value="Mali">Mali</option>
                            <option value="Malta">Malta</option>
                            <option value="Marshall Islands">Marshall Islands</option>
                            <option value="Mauritania">Mauritania</option>
                            <option value="Mauritius">Mauritius</option>
                            <option value="Mexico">Mexico</option>
                            <option value="Micronesia">Micronesia</option>
                            <option value="Moldova">Moldova</option>
                            <option value="Monaco">Monaco</option>
                            <option value="Mongolia">Mongolia</option>
                            <option value="Montenegro">Montenegro</option>
                            <option value="Morocco">Morocco</option>
                            <option value="Mozambique">Mozambique</option>
                            <option value="Myanmar">Myanmar</option>
                            <option value="Namibia">Namibia</option>
                            <option value="Nauru">Nauru</option>
                            <option value="Nepal">Nepal</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="Nicaragua">Nicaragua</option>
                            <option value="Niger">Niger</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="North Korea">North Korea</option>
                            <option value="North Macedonia">North Macedonia</option>
                            <option value="Norway">Norway</option>
                            <option value="Oman">Oman</option>
                            <option value="Pakistan">Pakistan</option>
                            <option value="Palau">Palau</option>
                            <option value="Palestine">Palestine</option>
                            <option value="Panama">Panama</option>
                            <option value="Papua New Guinea">Papua New Guinea</option>
                            <option value="Paraguay">Paraguay</option>
                            <option value="Peru">Peru</option>
                            <option value="Philippines">Philippines</option>
                            <option value="Poland">Poland</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Qatar">Qatar</option>
                            <option value="Romania">Romania</option>
                            <option value="Russia">Russia</option>
                            <option value="Rwanda">Rwanda</option>
                            <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                            <option value="Saint Lucia">Saint Lucia</option>
                            <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                            <option value="Samoa">Samoa</option>
                            <option value="San Marino">San Marino</option>
                            <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                            <option value="Saudi Arabia">Saudi Arabia</option>
                            <option value="Senegal">Senegal</option>
                            <option value="Serbia">Serbia</option>
                            <option value="Seychelles">Seychelles</option>
                            <option value="Sierra Leone">Sierra Leone</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Slovakia">Slovakia</option>
                            <option value="Slovenia">Slovenia</option>
                            <option value="Solomon Islands">Solomon Islands</option>
                            <option value="Somalia">Somalia</option>
                            <option value="South Africa">South Africa</option>
                            <option value="South Korea">South Korea</option>
                            <option value="South Sudan">South Sudan</option>
                            <option value="Spain">Spain</option>
                            <option value="Sri Lanka">Sri Lanka</option>
                            <option value="Sudan">Sudan</option>
                            <option value="Suriname">Suriname</option>
                            <option value="Sweden">Sweden</option>
                            <option value="Switzerland">Switzerland</option>
                            <option value="Syria">Syria</option>
                            <option value="Taiwan">Taiwan</option>
                            <option value="Tajikistan">Tajikistan</option>
                            <option value="Tanzania">Tanzania</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Togo">Togo</option>
                            <option value="Tonga">Tonga</option>
                            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                            <option value="Tunisia">Tunisia</option>
                            <option value="Turkey">Turkey</option>
                            <option value="Turkmenistan">Turkmenistan</option>
                            <option value="Tuvalu">Tuvalu</option>
                            <option value="Uganda">Uganda</option>
                            <option value="Ukraine">Ukraine</option>
                            <option value="United Arab Emirates">United Arab Emirates</option>
                            <option value="Uruguay">Uruguay</option>
                            <option value="Uzbekistan">Uzbekistan</option>
                            <option value="Vanuatu">Vanuatu</option>
                            <option value="Vatican City">Vatican City</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Vietnam">Vietnam</option>
                            <option value="Yemen">Yemen</option>
                            <option value="Zambia">Zambia</option>
                            <option value="Zimbabwe">Zimbabwe</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>


                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="terms" name="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="#" class="link">Terms of Service</a> and <a href="#" class="link">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary btn-large btn-full">
                        Create My Account
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php" class="link">Sign in here</a></p>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
