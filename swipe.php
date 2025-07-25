<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Match - HerMatchUp</title>
    <link rel="stylesheet" href="css/swipeCss.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="matches.html" class="nav-link">Matches</a>
            </div>
            <div class="nav-buttons">
                <button class="btn-secondary" onclick="window.location.href='matches.html'">
                    <i class="fas fa-users"></i>
                    My Matches
                </button>
                <button class="btn-secondary" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                Log Out
            </button>
            </div>
        </div>
    </nav>

    <!-- Swipe Interface -->
    <section class="swipe-section">
        <div class="swipe-container">
            <div class="swipe-header">
                <h1>Find Your Perfect Mentor</h1>
                <p>Swipe right to connect, left to pass</p>
            </div>

            <!-- Add Filters Section -->
            <div class="filters-section">
                <button class="filters-toggle" onclick="toggleFilters()">
                    <i class="fas fa-filter"></i>
                    Filters
                    <span class="filter-count" id="filter-count">0</span>
                </button>
                
                <div class="filters-panel" id="filters-panel">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>STEM Field</label>
                            <select id="field-filter" onchange="applyFilters()">
                                <option value="">All Fields</option>
                                <option value="aerospace-engineering">Aerospace Engineering</option>
                                <option value="artificial-intelligence">Artificial Intelligence</option>
                                <option value="biochemistry">Biochemistry</option>
                                <option value="bioengineering">Bioengineering</option>
                                <option value="biology">Biology</option>
                                <option value="chemistry">Chemistry</option>
                                <option value="computer-science">Computer Science</option>
                                <option value="data-science">Data Science</option>
                                <option value="electrical-engineering">Electrical Engineering</option>
                                <option value="environmental-science">Environmental Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="mechanical-engineering">Mechanical Engineering</option>
                                <option value="physics">Physics</option>
                                <option value="software-engineering">Software Engineering</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Experience Level</label>
                            <select id="experience-filter" onchange="applyFilters()">
                                <option value="">Any Level</option>
                                <option value="0-1-years">0-1 Years</option>
                                <option value="1-2-years">1-2 Years</option>
                                <option value="2-3-years">2-3 Years</option>
                                <option value="3-5-years">3-5 Years</option>
                                <option value="5-plus-years">5+ Years</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Communication Tone</label>
                            <select id="communication-tone-filter" onchange="applyFilters()">
                                <option value="">Any Style</option>
                                <option value="friendly-casual">Friendly & Casual</option>
                                <option value="professional-formal">Professional & Formal</option>
                                <option value="supportive-empathetic">Supportive & Empathetic</option>
                                <option value="direct-straightforward">Direct & Straightforward</option>
                                <option value="analytical-detailed">Analytical & Detailed</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Communication Frequency</label>
                            <select id="communication-frequency-filter" onchange="applyFilters()">
                                <option value="">Any Frequency</option>
                                <option value="frequent-checkins">Frequent Check-ins</option>
                                <option value="occasional-messages">Occasional Messages</option>
                                <option value="as-needed">As-needed Communication</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Personality Type</label>
                            <select id="personality-filter" onchange="applyFilters()">
                                <option value="">Any Type</option>
                                <option value="INTJ">INTJ - The Architect</option>
                                <option value="INTP">INTP - The Thinker</option>
                                <option value="ENTJ">ENTJ - The Commander</option>
                                <option value="ENTP">ENTP - The Debater</option>
                                <option value="INFJ">INFJ - The Advocate</option>
                                <option value="INFP">INFP - The Mediator</option>
                                <option value="ENFJ">ENFJ - The Protagonist</option>
                                <option value="ENFP">ENFP - The Campaigner</option>
                                <option value="ISTJ">ISTJ - The Logistician</option>
                                <option value="ISFJ">ISFJ - The Protector</option>
                                <option value="ESTJ">ESTJ - The Executive</option>
                                <option value="ESFJ">ESFJ - The Consul</option>
                                <option value="ISTP">ISTP - The Virtuoso</option>
                                <option value="ISFP">ISFP - The Adventurer</option>
                                <option value="ESTP">ESTP - The Entrepreneur</option>
                                <option value="ESFP">ESFP - The Entertainer</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Location</label>
                            <select id="location-filter" onchange="applyFilters()">
                                <option value="">Any Location</option>
                                <option value="remote">Remote Only</option>
                                <option value="united-states">United States</option>
                                <option value="canada">Canada</option>
                                <option value="united-kingdom">United Kingdom</option>
                                <option value="germany">Germany</option>
                                <option value="france">France</option>
                                <option value="australia">Australia</option>
                                <option value="india">India</option>
                                <option value="singapore">Singapore</option>
                                <option value="japan">Japan</option>
                                <option value="south-korea">South Korea</option>
                                <option value="netherlands">Netherlands</option>
                                <option value="sweden">Sweden</option>
                                <option value="switzerland">Switzerland</option>
                                <option value="brazil">Brazil</option>
                                <option value="mexico">Mexico</option>
                                <option value="spain">Spain</option>
                                <option value="italy">Italy</option>
                                <option value="china">China</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Response Time</label>
                            <select id="response-time-filter" onchange="applyFilters()">
                                <option value="">Any Timing</option>
                                <option value="Within 24 hours">Within 24 hours</option>
                                <option value="Within 48 hours">Within 48 hours</option>
                                <option value="A few days">A few days</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button class="btn-secondary" onclick="clearFilters()">Clear All</button>
                        <button class="btn-primary" onclick="toggleFilters()">Apply Filters</button>
                    </div>
                </div>
            </div>

            <div class="swipe-area" id="swipe-area">
                <div class="swipe-card" id="card-1">
                    <div class="card-image">
                        <div class="avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="card-content">
                        <div class="card-header">
                            <h2>Dr. Sarah Chen</h2>
                            <span class="age">32</span>
                        </div>
                        <p class="title">AI Research Scientist at Google</p>
                        <p class="bio">PhD in Computer Science. Passionate about machine learning and helping women break into AI research. Available for weekly video calls.</p>
                        
                        <div class="specializations">
                            <span class="tag">Machine Learning</span>
                            <span class="tag">PhD Guidance</span>
                            <span class="tag">Research</span>
                            <span class="tag">Career Transition</span>
                        </div>

                        <div class="compatibility">
                            <div class="compatibility-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span>PhD Level</span>
                            </div>
                            <div class="compatibility-item">
                                <i class="fas fa-clock"></i>
                                <span>Evenings Available</span>
                            </div>
                            <div class="compatibility-item">
                                <i class="fas fa-video"></i>
                                <span>Video Calls</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="swipe-card" id="card-2">
                    <div class="card-image">
                        <div class="avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="card-content">
                        <div class="card-header">
                            <h2>Maria Rodriguez</h2>
                            <span class="age">28</span>
                        </div>
                        <p class="title">Senior Software Engineer at Microsoft</p>
                        <p class="bio">Full-stack developer with 6 years experience. Love mentoring junior developers and helping with coding interviews. Flexible scheduling.</p>
                        
                        <div class="specializations">
                            <span class="tag">Full-Stack Development</span>
                            <span class="tag">Interview Prep</span>
                            <span class="tag">JavaScript</span>
                            <span class="tag">Career Growth</span>
                        </div>

                        <div class="compatibility">
                            <div class="compatibility-item">
                                <i class="fas fa-code"></i>
                                <span>Coding Focus</span>
                            </div>
                            <div class="compatibility-item">
                                <i class="fas fa-clock"></i>
                                <span>Flexible Hours</span>
                            </div>
                            <div class="compatibility-item">
                                <i class="fas fa-comments"></i>
                                <span>Chat & Video</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="swipe-card" id="card-3">
                    <div class="card-image">
                        <div class="avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="card-content">
                        <div class="card-header">
                            <h2>Dr. Priya Patel</h2>
                            <span class="age">35</span>
                        </div>
                        <p class="title">Biomedical Engineer & Startup Founder</p>
                        <p class="bio">Founded two successful biotech startups. Mentor for women in bioengineering and entrepreneurship. Weekend availability preferred.</p>
                        
                        <div class="specializations">
                            <span class="tag">Bioengineering</span>
                            <span class="tag">Entrepreneurship</span>
                            <span class="tag">Startup Advice</span>
                            <span class="tag">Leadership</span>
                        </div>

                        <div class="compatibility">
                            <div class="compatibility-item">
                                <i class="fas fa-rocket"></i>
                                <span>Startup Focus</span>
                            </div>
                            <div class="compatibility-item">
                                <i class="fas fa-calendar-weekend"></i>
                                <span>Weekends</span>
                            </div>
                            <div class="compatibility-item">
                                <i class="fas fa-phone"></i>
                                <span>Phone Calls</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="no-more-cards">
                    <div class="no-cards-content">
                        <i class="fas fa-heart"></i>
                        <h2>That's all for now!</h2>
                        <p>Check back later for more potential matches, or view your current connections.</p>
                        <button class="btn-primary" onclick="window.location.href='matches.html'">
                            View My Matches
                        </button>
                    </div>
                </div>
            </div>

            <div class="swipe-actions">
                <button class="action-btn reject-btn" onclick="swipeCard('left')">
                    <i class="fas fa-times"></i>
                </button>
                <button class="action-btn super-like-btn" onclick="superLike()">
                    <i class="fas fa-star"></i>
                </button>
                <button class="action-btn like-btn" onclick="swipeCard('right')">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Match Modal -->
    <div class="match-modal" id="match-modal">
        <div class="match-content">
            <div class="match-header">
                <h2>It's a Match!</h2>
                <i class="fas fa-heart match-heart"></i>
            </div>
            <div class="match-profiles">
                <div class="match-profile">
                    <div class="match-avatar" id="user-avatar"></div>
                    <span>You</span>
                </div>
                <div class="match-profile">
                    <div class="match-avatar" id="match-avatar"></div>
                    <span id="match-name">Sarah</span>
                </div>
            </div>
            <p>You and <span id="match-name-text">Sarah</span> have both shown interest in connecting!</p>
            <div class="match-actions">
                <button class="btn-secondary" onclick="closeMatchModal()">Keep Swiping</button>
                <button class="btn-primary" onclick="startConversation()">Start Conversation</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
