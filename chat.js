// Chat functionality
let currentConversationId = null
let pollingInterval = null
let lastMessageId = 0

// Check if user is logged in
function isUserLoggedIn() {
  return localStorage.getItem("isLoggedIn") === "true" || sessionStorage.getItem("user_id") !== null
}

// Check authentication and update navbar
function checkAuthentication() {
  const isLoggedIn = isUserLoggedIn()
  const authButtons = document.getElementById("auth-buttons")
  const userButtons = document.getElementById("user-buttons")
  const messagesNav = document.getElementById("messages-nav")
  const matchesNav = document.getElementById("matches-nav")
  const profileNav = document.getElementById("profile-nav")
  const userGreeting = document.getElementById("user-greeting")

  if (isLoggedIn) {
    // Show logged-in navigation
    if (authButtons) authButtons.style.display = "none"
    if (userButtons) userButtons.style.display = "flex"
    if (messagesNav) messagesNav.style.display = "block"
    if (matchesNav) matchesNav.style.display = "block"
    if (profileNav) profileNav.style.display = "block"

    // Set user greeting
    const userData = JSON.parse(localStorage.getItem("userData") || "{}")
    if (userGreeting && userData.name) {
      userGreeting.textContent = `Hi, ${userData.name.split(" ")[0]}!`
    }

    // Update unread message count
    updateUnreadCount()
  } else {
    // Show guest navigation
    if (authButtons) authButtons.style.display = "flex"
    if (userButtons) userButtons.style.display = "none"
    if (messagesNav) messagesNav.style.display = "none"
    if (matchesNav) matchesNav.style.display = "none"
    if (profileNav) profileNav.style.display = "none"
  }
}

// Logout function
function logout() {
  localStorage.removeItem("isLoggedIn")
  localStorage.removeItem("userData")
  sessionStorage.clear()

  // Update online status
  fetch("php/chat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=update_online_status&is_online=false",
  })

  window.location.href = "index.html"
}

// Load conversations
async function loadConversations() {
  try {
    const response = await fetch("php/chat.php?action=get_conversations")
    const data = await response.json()

    if (data.error) {
      console.error("Error loading conversations:", data.error)
      return
    }

    const conversationsList = document.getElementById("conversations-list")

    if (data.conversations.length === 0) {
      conversationsList.innerHTML = `
                <div class="no-conversations">
                    <i class="fas fa-comments" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                    <h3>No conversations yet</h3>
                    <p>Start a new conversation to begin mentoring!</p>
                    <button class="btn-primary" onclick="openNewChatModal()">
                        <i class="fas fa-plus"></i> Start New Chat
                    </button>
                </div>
            `
      return
    }

    conversationsList.innerHTML = data.conversations
      .map(
        (conv) => `
            <div class="conversation-item ${conv.unread_count > 0 ? "unread" : ""}" 
                 onclick="openConversation(${conv.conversation_id}, '${conv.full_name}', '${conv.avatar_color}', ${conv.is_online})">
                <div class="conversation-avatar" style="background: ${conv.avatar_color}">
                    ${conv.full_name
                      .split(" ")
                      .map((n) => n[0])
                      .join("")
                      .substring(0, 2)}
                    ${conv.is_online ? '<div class="online-indicator"></div>' : ""}
                </div>
                <div class="conversation-info">
                    <div class="conversation-name">
                        ${conv.full_name}
                        <span class="user-type-badge">${conv.user_type}</span>
                    </div>
                    <div class="conversation-preview">${conv.last_message}</div>
                </div>
                <div class="conversation-meta">
                    <div class="conversation-time">${formatTime(conv.last_message_time)}</div>
                    ${conv.unread_count > 0 ? `<div class="unread-badge">${conv.unread_count}</div>` : ""}
                </div>
            </div>
        `,
      )
      .join("")
  } catch (error) {
    console.error("Error loading conversations:", error)
  }
}

// Open conversation
async function openConversation(conversationId, userName, avatarColor, isOnline) {
  currentConversationId = conversationId

  // Update chat header
  document.getElementById("chat-user-name").textContent = userName
  document.getElementById("chat-user-status").textContent = isOnline ? "Online" : "Offline"
  document.getElementById("chat-avatar").style.background = avatarColor
  document.getElementById("chat-avatar").textContent = userName
    .split(" ")
    .map((n) => n[0])
    .join("")
    .substring(0, 2)

  // Show chat view
  document.getElementById("conversations-view").style.display = "none"
  document.getElementById("chat-view").style.display = "flex"

  // Load messages
  await loadMessages(conversationId)

  // Focus message input
  document.getElementById("message-input").focus()
}

// Load messages
async function loadMessages(conversationId) {
  try {
    const response = await fetch(`php/chat.php?action=get_messages&conversation_id=${conversationId}`)
    const data = await response.json()

    if (data.error) {
      console.error("Error loading messages:", data.error)
      return
    }

    const messagesContainer = document.getElementById("messages-container")
    const currentUserId = getCurrentUserId()

    messagesContainer.innerHTML = data.messages
      .map(
        (msg) => `
            <div class="message ${msg.sender_id == currentUserId ? "sent" : "received"}">
                <div class="message-content">
                    <p class="message-text">${escapeHtml(msg.message)}</p>
                    <span class="message-time">${formatTime(msg.created_at)}</span>
                </div>
            </div>
        `,
      )
      .join("")

    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight

    // Update last message ID for polling
    if (data.messages.length > 0) {
      lastMessageId = Math.max(...data.messages.map((m) => m.id))
    }
  } catch (error) {
    console.error("Error loading messages:", error)
  }
}

// Send message
async function sendMessage() {
  const messageInput = document.getElementById("message-input")
  const message = messageInput.value.trim()

  if (!message || !currentConversationId) return

  const sendBtn = document.querySelector(".send-btn")
  sendBtn.disabled = true

  try {
    const response = await fetch("php/chat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=send_message&conversation_id=${currentConversationId}&message=${encodeURIComponent(message)}`,
    })

    const data = await response.json()

    if (data.error) {
      alert("Error sending message: " + data.error)
      return
    }

    // Clear input
    messageInput.value = ""

    // Reload messages
    await loadMessages(currentConversationId)
  } catch (error) {
    console.error("Error sending message:", error)
    alert("Error sending message. Please try again.")
  } finally {
    sendBtn.disabled = false
  }
}

// Show conversations view
function showConversations() {
  document.getElementById("chat-view").style.display = "none"
  document.getElementById("conversations-view").style.display = "block"
  currentConversationId = null

  // Reload conversations to update unread counts
  loadConversations()
}

// Open new chat modal
async function openNewChatModal() {
  const modal = document.getElementById("new-chat-modal")
  modal.classList.add("show")

  // Load available users
  await loadAvailableUsers()
}

// Close new chat modal
function closeNewChatModal() {
  const modal = document.getElementById("new-chat-modal")
  modal.classList.remove("show")
}

// Load available users
async function loadAvailableUsers(search = "") {
  try {
    const response = await fetch(`php/chat.php?action=get_users&search=${encodeURIComponent(search)}`)
    const data = await response.json()

    if (data.error) {
      console.error("Error loading users:", data.error)
      return
    }

    const usersList = document.getElementById("users-list")

    if (data.users.length === 0) {
      usersList.innerHTML = '<p style="text-align: center; color: #666;">No users found</p>'
      return
    }

    usersList.innerHTML = data.users
      .map(
        (user) => `
            <div class="user-item" onclick="startNewConversation(${user.id})">
                <div class="conversation-avatar" style="background: ${user.avatar_color}">
                    ${user.full_name
                      .split(" ")
                      .map((n) => n[0])
                      .join("")
                      .substring(0, 2)}
                    ${user.is_online ? '<div class="online-indicator"></div>' : ""}
                </div>
                <div class="conversation-info">
                    <div class="conversation-name">
                        ${user.full_name}
                        <span class="user-type-badge">${user.user_type}</span>
                    </div>
                    <div class="conversation-preview">${user.stem_field}</div>
                </div>
            </div>
        `,
      )
      .join("")
  } catch (error) {
    console.error("Error loading users:", error)
  }
}

// Start new conversation
async function startNewConversation(userId) {
  try {
    const response = await fetch("php/chat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=start_conversation&user_id=${userId}`,
    })

    const data = await response.json()

    if (data.error) {
      alert("Error starting conversation: " + data.error)
      return
    }

    // Close modal
    closeNewChatModal()

    // Reload conversations
    await loadConversations()

    // Open the new conversation
    // Find the conversation in the list and click it
    setTimeout(() => {
      const conversationItems = document.querySelectorAll(".conversation-item")
      if (conversationItems.length > 0) {
        conversationItems[0].click() // Click the first (newest) conversation
      }
    }, 500)
  } catch (error) {
    console.error("Error starting conversation:", error)
    alert("Error starting conversation. Please try again.")
  }
}

// Update unread message count in navbar
async function updateUnreadCount() {
  try {
    const response = await fetch("php/chat.php?action=get_unread_count")
    const data = await response.json()

    if (data.error) return

    const badge = document.getElementById("nav-message-badge")
    if (badge) {
      if (data.unread_count > 0) {
        badge.textContent = data.unread_count > 99 ? "99+" : data.unread_count
        badge.style.display = "inline"
      } else {
        badge.style.display = "none"
      }
    }
  } catch (error) {
    console.error("Error updating unread count:", error)
  }
}

// Start polling for new messages
function startPolling() {
  // Update online status
  fetch("php/chat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=update_online_status&is_online=true",
  })

  // Poll every 2 seconds
  pollingInterval = setInterval(async () => {
    if (currentConversationId) {
      await loadMessages(currentConversationId)
    }
    await updateUnreadCount()
  }, 2000)

  // Update online status every 30 seconds
  setInterval(() => {
    fetch("php/chat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "action=update_online_status&is_online=true",
    })
  }, 30000)
}

// Utility functions
function getCurrentUserId() {
  // This should return the actual user ID from your session/auth system
  // For demo purposes, we'll use a stored value
  return sessionStorage.getItem("user_id") || "1"
}

function formatTime(timestamp) {
  if (!timestamp) return ""

  const now = new Date()
  const time = new Date(timestamp)
  const diffMs = now - time
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return "Just now"
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`

  return time.toLocaleDateString()
}

function escapeHtml(text) {
  const div = document.createElement("div")
  div.textContent = text
  return div.innerHTML
}

// Event listeners
document.addEventListener("DOMContentLoaded", () => {
  // Message input enter key
  const messageInput = document.getElementById("message-input")
  if (messageInput) {
    messageInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
      }
    })
  }

  // User search in modal
  const userSearch = document.getElementById("user-search")
  if (userSearch) {
    let searchTimeout
    userSearch.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadAvailableUsers(this.value)
      }, 300)
    })
  }

  // Conversation search
  const conversationSearch = document.getElementById("conversation-search")
  if (conversationSearch) {
    conversationSearch.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase()
      const conversations = document.querySelectorAll(".conversation-item")

      conversations.forEach((conv) => {
        const name = conv.querySelector(".conversation-name").textContent.toLowerCase()
        const preview = conv.querySelector(".conversation-preview").textContent.toLowerCase()

        if (name.includes(searchTerm) || preview.includes(searchTerm)) {
          conv.style.display = "flex"
        } else {
          conv.style.display = "none"
        }
      })
    })
  }

  // Close modal when clicking outside
  document.addEventListener("click", (e) => {
    const modal = document.getElementById("new-chat-modal")
    if (e.target === modal) {
      closeNewChatModal()
    }
  })
})

// Cleanup on page unload
window.addEventListener("beforeunload", () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }

  // Update offline status
  fetch("php/chat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=update_online_status&is_online=false",
  })
})

// Additional functions for chat actions
function scheduleCall() {
  alert("Video call feature coming soon!")
}

function viewProfile() {
  alert("Profile view feature coming soon!")
}
