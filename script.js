// Global variables
let currentCardIndex = 0
let isDragging = false
let startX = 0
let startY = 0
let currentX = 0
let currentY = 0

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  initializeNavigation()
  initializeSwipeCards()
  initializeProfileModal()
  initializeMatchesTabs()
  initializeChatModal()
  initializeScrollEffects()
  initializeAuthForms()
  initializeProfilePhoto()
})

// Navigation functionality
function initializeNavigation() {
  const hamburger = document.getElementById("hamburger")
  const navMenu = document.getElementById("nav-menu")

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("active")
      hamburger.classList.toggle("active")
    })
  }

  // Smooth scrolling for navigation links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
}

// Scroll to section function
function scrollToSection(sectionId) {
  const section = document.getElementById(sectionId)
  if (section) {
    section.scrollIntoView({
      behavior: "smooth",
      block: "start",
    })
  }
}

// Swipe cards functionality
function initializeSwipeCards() {
  const swipeArea = document.getElementById("swipe-area")
  if (!swipeArea) return

  const cards = document.querySelectorAll(".swipe-card")

  cards.forEach((card, index) => {
    // Set z-index for stacking
    card.style.zIndex = cards.length - index

    // Add touch and mouse event listeners
    card.addEventListener("mousedown", handleStart)
    card.addEventListener("touchstart", handleStart)

    card.addEventListener("mousemove", handleMove)
    card.addEventListener("touchmove", handleMove)

    card.addEventListener("mouseup", handleEnd)
    card.addEventListener("touchend", handleEnd)

    card.addEventListener("mouseleave", handleEnd)
  })
}

function handleStart(e) {
  if (e.target.closest(".swipe-card") !== getCurrentCard()) return

  isDragging = true
  const card = getCurrentCard()
  card.classList.add("dragging")

  const clientX = e.type === "mousedown" ? e.clientX : e.touches[0].clientX
  const clientY = e.type === "mousedown" ? e.clientY : e.touches[0].clientY

  startX = clientX
  startY = clientY
  currentX = clientX
  currentY = clientY
}

function handleMove(e) {
  if (!isDragging) return

  e.preventDefault()

  const clientX = e.type === "mousemove" ? e.clientX : e.touches[0].clientX
  const clientY = e.type === "mousemove" ? e.clientY : e.touches[0].clientY

  currentX = clientX
  currentY = clientY

  const deltaX = currentX - startX
  const deltaY = currentY - startY

  const card = getCurrentCard()
  const rotation = deltaX * 0.1

  card.style.transform = `translate(${deltaX}px, ${deltaY}px) rotate(${rotation}deg)`
  card.style.opacity = 1 - Math.abs(deltaX) / 300
}

function handleEnd(e) {
  if (!isDragging) return

  isDragging = false
  const card = getCurrentCard()
  card.classList.remove("dragging")

  const deltaX = currentX - startX
  const threshold = 100

  if (Math.abs(deltaX) > threshold) {
    if (deltaX > 0) {
      swipeCard("right")
    } else {
      swipeCard("left")
    }
  } else {
    // Snap back to center
    card.style.transform = ""
    card.style.opacity = ""
  }
}

function getCurrentCard() {
  return document.getElementById(`card-${currentCardIndex + 1}`)
}

function swipeCard(direction) {
  const card = getCurrentCard()
  if (!card) return

  card.classList.add(`swiped-${direction}`)

  if (direction === "right") {
    // Check for match (simulate 30% match rate)
    if (Math.random() < 0.3) {
      setTimeout(() => showMatchModal(card), 500)
    }
  }

  setTimeout(() => {
    card.style.display = "none"
    currentCardIndex++

    if (currentCardIndex >= document.querySelectorAll(".swipe-card").length) {
      showNoMoreCards()
    }
  }, 300)
}

function superLike() {
  const card = getCurrentCard()
  if (!card) return

  // Add super like animation
  card.style.transform = "translateY(-100px) scale(1.1)"
  card.style.opacity = "0"

  // Always show match for super likes
  setTimeout(() => showMatchModal(card), 500)

  setTimeout(() => {
    card.style.display = "none"
    currentCardIndex++

    if (currentCardIndex >= document.querySelectorAll(".swipe-card").length) {
      showNoMoreCards()
    }
  }, 300)
}

function showNoMoreCards() {
  const noMoreCards = document.querySelector(".no-more-cards")
  if (noMoreCards) {
    noMoreCards.classList.add("show")
  }
}

function showMatchModal(card) {
  const modal = document.getElementById("match-modal")
  const matchName = card.querySelector("h2").textContent.split(" ")[1] // Get first name

  document.getElementById("match-name").textContent = matchName
  document.getElementById("match-name-text").textContent = matchName

  // Set avatars (you can customize this based on the card)
  const userAvatar = document.getElementById("user-avatar")
  const matchAvatar = document.getElementById("match-avatar")

  userAvatar.style.background = "linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
  matchAvatar.style.background = card.querySelector(".avatar").style.background

  modal.classList.add("show")
}

function closeMatchModal() {
  const modal = document.getElementById("match-modal")
  modal.classList.remove("show")
}

function startConversation() {
  closeMatchModal()
  window.location.href = "matches.html"
}

// Profile modal functionality
function initializeProfileModal() {
  const editBtn = document.getElementById("edit-profile-btn")
  const modal = document.getElementById("edit-profile-modal")

  if (editBtn && modal) {
    editBtn.addEventListener("click", openEditModal)
  }

  // Add tag functionality
  const specializationInput = document.getElementById("specialization-input")
  if (specializationInput) {
    specializationInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault()
        addSpecialization()
      }
    })
  }

  // Remove tag functionality
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("fa-times") && e.target.closest(".tag.removable")) {
      e.target.closest(".tag.removable").remove()
    }
  })
}

function openEditModal() {
  const modal = document.getElementById("edit-profile-modal")
  modal.classList.add("show")
}

function closeEditModal() {
  const modal = document.getElementById("edit-profile-modal")
  modal.classList.remove("show")
}

function addSpecialization() {
  const input = document.getElementById("specialization-input")
  const container = document.getElementById("current-specializations")

  if (input.value.trim()) {
    const tag = document.createElement("span")
    tag.className = "tag removable"
    tag.innerHTML = `${input.value.trim()} <i class="fas fa-times"></i>`

    container.appendChild(tag)
    input.value = ""
  }
}

function saveProfile() {
  // Get form values
  const name = document.getElementById("edit-name").value
  const title = document.getElementById("edit-title").value
  const location = document.getElementById("edit-location").value
  const bio = document.getElementById("edit-bio").value

  // Update profile display
  document.getElementById("profile-name").textContent = name
  document.getElementById("profile-title").textContent = title
  document.getElementById("profile-location").innerHTML = `<i class="fas fa-map-marker-alt"></i> ${location}`
  document.getElementById("profile-bio").textContent = bio

  // Update specializations
  const currentTags = document.getElementById("current-specializations")
  const displayTags = document.getElementById("specialization-tags")

  displayTags.innerHTML = ""
  currentTags.querySelectorAll(".tag").forEach((tag) => {
    const newTag = document.createElement("span")
    newTag.className = "tag"
    newTag.textContent = tag.textContent.replace("×", "").trim()
    displayTags.appendChild(newTag)
  })

  closeEditModal()

  // Show success message (you can customize this)
  showNotification("Profile updated successfully!")
}

// Matches tabs functionality
function initializeMatchesTabs() {
  const tabBtns = document.querySelectorAll(".tab-btn")

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const tabName = this.textContent.toLowerCase().split(" ")[0]
      switchTab(tabName)
    })
  })
}

function switchTab(tabName) {
  // Update tab buttons
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.classList.remove("active")
  })

  event.target.classList.add("active")

  // Update tab content
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.remove("active")
  })

  const targetTab = document.getElementById(`${tabName}-tab`)
  if (targetTab) {
    targetTab.classList.add("active")
  }
}

// Chat modal functionality
function initializeChatModal() {
  const messageInput = document.getElementById("message-input")

  if (messageInput) {
    messageInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        sendMessage()
      }
    })
  }
}

function openChat(mentorName) {
  const modal = document.getElementById("chat-modal")
  const chatName = document.getElementById("chat-name")
  const chatAvatar = document.getElementById("chat-avatar")

  // Set mentor info based on name
  const mentorInfo = {
    sarah: {
      name: "Dr. Sarah Chen",
      avatar: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
    },
    maria: {
      name: "Maria Rodriguez",
      avatar: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)",
    },
    priya: {
      name: "Dr. Priya Patel",
      avatar: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)",
    },
  }

  const info = mentorInfo[mentorName]
  if (info) {
    chatName.textContent = info.name
    chatAvatar.style.background = info.avatar
  }

  modal.classList.add("show")
}

function closeChat() {
  const modal = document.getElementById("chat-modal")
  modal.classList.remove("show")
}

function sendMessage() {
  const input = document.getElementById("message-input")
  const messagesContainer = document.getElementById("chat-messages")

  if (input.value.trim()) {
    const messageDiv = document.createElement("div")
    messageDiv.className = "message sent"

    const now = new Date()
    const timeString = now.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })

    messageDiv.innerHTML = `
            <div class="message-content">
                <p>${input.value}</p>
                <span class="message-time">${timeString}</span>
            </div>
        `

    messagesContainer.appendChild(messageDiv)
    messagesContainer.scrollTop = messagesContainer.scrollHeight

    input.value = ""

    // Simulate response after 2 seconds
    setTimeout(() => {
      const responseDiv = document.createElement("div")
      responseDiv.className = "message received"

      const responses = [
        "That's a great question! Let me think about that...",
        "I'd be happy to help you with that!",
        "That reminds me of when I was starting out...",
        "Have you considered looking into...?",
        "I think you're on the right track!",
      ]

      const randomResponse = responses[Math.floor(Math.random() * responses.length)]

      responseDiv.innerHTML = `
                <div class="message-content">
                    <p>${randomResponse}</p>
                    <span class="message-time">${new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</span>
                </div>
            `

      messagesContainer.appendChild(responseDiv)
      messagesContainer.scrollTop = messagesContainer.scrollHeight
    }, 2000)
  }
}

function scheduleCall(mentorName) {
  showNotification(`Video call scheduled with ${mentorName}!`)
}

function viewHistory(mentorName) {
  showNotification(`Viewing mentorship history with ${mentorName}`)
}

// Scroll effects
function initializeScrollEffects() {
  // Navbar scroll effect
  window.addEventListener("scroll", () => {
    const navbar = document.querySelector(".navbar")
    if (window.scrollY > 50) {
      navbar.style.background = "rgba(255, 255, 255, 0.98)"
      navbar.style.boxShadow = "0 2px 20px rgba(0, 0, 0, 0.1)"
    } else {
      navbar.style.background = "rgba(255, 255, 255, 0.95)"
      navbar.style.boxShadow = "none"
    }
  })

  // Intersection Observer for animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in")
      }
    })
  }, observerOptions)

  // Observe elements for animation
  document.querySelectorAll(".feature-card, .step, .testimonial-card").forEach((el) => {
    observer.observe(el)
  })
}

// Utility functions
function showNotification(message, type = "success") {
  const notification = document.createElement("div")
  notification.className = `notification ${type}`
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === "error" ? "#ef4444" : "#10b981"};
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    z-index: 3000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
  `
  notification.textContent = message

  document.body.appendChild(notification)

  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 100)

  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      if (document.body.contains(notification)) {
        document.body.removeChild(notification)
      }
    }, 300)
  }, 3000)
}

// Prevent default drag behavior on images
document.addEventListener("dragstart", (e) => {
  if (e.target.tagName === "IMG") {
    e.preventDefault()
  }
})

// Handle window resize
window.addEventListener("resize", () => {
  // Reset any ongoing swipe operations
  if (isDragging) {
    isDragging = false
    const card = getCurrentCard()
    if (card) {
      card.classList.remove("dragging")
      card.style.transform = ""
      card.style.opacity = ""
    }
  }
})

// Keyboard navigation
document.addEventListener("keydown", (e) => {
  // Only handle keyboard events on swipe page
  if (!document.getElementById("swipe-area")) return

  switch (e.key) {
    case "ArrowLeft":
      e.preventDefault()
      swipeCard("left")
      break
    case "ArrowRight":
      e.preventDefault()
      swipeCard("right")
      break
    case "ArrowUp":
      e.preventDefault()
      superLike()
      break
    case "Escape":
      // Close any open modals
      document.querySelectorAll(".modal.show").forEach((modal) => {
        modal.classList.remove("show")
      })
      break
  }
})

// Add new functionality for authentication and filtering

// Enhanced profile photo upload
function initializeProfilePhoto() {
  const changePhotoBtn = document.querySelector(".change-photo-btn")
  const photoInput = document.getElementById("profile-photo")
  const avatarPlaceholder = document.querySelector(".avatar-placeholder")

  if (changePhotoBtn && photoInput) {
    changePhotoBtn.addEventListener("click", () => {
      photoInput.click()
    })

    photoInput.addEventListener("change", (e) => {
      const file = e.target.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
          avatarPlaceholder.style.backgroundImage = `url(${e.target.result})`
          avatarPlaceholder.style.backgroundSize = "cover"
          avatarPlaceholder.style.backgroundPosition = "center"
          avatarPlaceholder.innerHTML = ""
        }
        reader.readAsDataURL(file)
      }
    })
  }
}

// Authentication form handling
function initializeAuthForms() {
  const signupForm = document.getElementById("signup-form")
  const loginForm = document.getElementById("login-form")

  if (signupForm) {
    signupForm.addEventListener("submit", handleSignup)
  }

  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin)
  }
}

function handleSignup(e) {
  e.preventDefault()
  const formData = new FormData(e.target)
  const userData = Object.fromEntries(formData)

  // Validate passwords match
  if (userData.password !== userData.confirmPassword) {
    showNotification("Passwords do not match", "error")
    return
  }

  // Validate password strength
  if (userData.password.length < 8) {
    showNotification("Password must be at least 8 characters long", "error")
    return
  }

  // Simulate API call
  setTimeout(() => {
    showNotification("Account created successfully!")
    // Store user data (in real app, this would be handled by backend)
    localStorage.setItem("userData", JSON.stringify(userData))
    window.location.href = "profile.html"
  }, 1000)
}

// function handleLogin(e) {
//   e.preventDefault()
//   const formData = new FormData(e.target)
//   const loginData = Object.fromEntries(formData)

//   const storedUserData = JSON.parse(localStorage.getItem("userData"))

//   if (
//     storedUserData &&
//     loginData.email === storedUserData.email &&
//     loginData.password === storedUserData.password
//   ) {
//     showNotification("Welcome back!")
//     localStorage.setItem("isLoggedIn", "true")
//     window.location.href = "swipe.html"
//   } else {
//     showNotification("Invalid email or password", "error")
//   }
// }

// Filter functionality
let activeFilters = {}

function toggleFilters() {
  const panel = document.getElementById("filters-panel")
  panel.classList.toggle("show")
}

function applyFilters() {
  const filters = {
    field: document.getElementById("field-filter")?.value || "",
    experience: document.getElementById("experience-filter")?.value || "",
    communicationTone: document.getElementById("communication-tone-filter")?.value || "",
    communicationFrequency: document.getElementById("communication-frequency-filter")?.value || "",
    personality: document.getElementById("personality-filter")?.value || "",
    location: document.getElementById("location-filter")?.value || "",
    responseTime: document.getElementById("response-time-filter")?.value || "",
  }

  activeFilters = Object.fromEntries(Object.entries(filters).filter(([key, value]) => value !== ""))

  updateFilterCount()
  filterCards()
}

function updateFilterCount() {
  const count = Object.keys(activeFilters).length
  const countElement = document.getElementById("filter-count")
  if (countElement) {
    countElement.textContent = count
    countElement.style.display = count > 0 ? "flex" : "none"
  }
}

function clearFilters() {
  activeFilters = {}

  // Reset all filter selects
  document.querySelectorAll(".filter-group select").forEach((select) => {
    select.value = ""
  })

  updateFilterCount()
  filterCards()
}

function filterCards() {
  // In a real app, this would filter the cards based on user data
  // For demo purposes, we'll just show a notification
  const filterCount = Object.keys(activeFilters).length
  if (filterCount > 0) {
    showNotification(`Applied ${filterCount} filter${filterCount > 1 ? "s" : ""}`)
  } else {
    showNotification("Showing all mentors")
  }
}
