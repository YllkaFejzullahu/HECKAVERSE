// Modern Chat Implementation with Axios and WebSocket-like polling
import axios from 'axios';
import Swal from 'sweetalert2';

class ChatManager {
    constructor() {
        this.currentConversationId = null;
        this.pollingInterval = null;
        this.lastMessageId = 0;
        this.pollDelay = 2000; // 2 seconds
        this.isPolling = false;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthentication();
        this.loadConversations();
    }

    setupEventListeners() {
        // Send message form
        const messageForm = document.getElementById('message-form');
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => this.handleSendMessage(e));
        }

        // Message input auto-resize
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            messageInput.addEventListener('input', this.autoResizeTextarea);
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.handleSendMessage(e);
                }
            });
        }

        // Conversation list clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.conversation-item')) {
                const conversationId = e.target.closest('.conversation-item').dataset.conversationId;
                this.selectConversation(conversationId);
            }
        });

        // Visibility change to pause/resume polling
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else if (this.currentConversationId) {
                this.startPolling();
            }
        });
    }

    async checkAuthentication() {
        try {
            const response = await axios.get('/api/auth-check.php');
            if (!response.data.authenticated) {
                window.location.href = '/login.php';
                return;
            }
            
            this.updateUserInfo(response.data.user);
        } catch (error) {
            console.error('Auth check failed:', error);
            this.showError('Authentication failed. Please log in again.');
            setTimeout(() => {
                window.location.href = '/login.php';
            }, 2000);
        }
    }

    updateUserInfo(user) {
        const userGreeting = document.getElementById('user-greeting');
        if (userGreeting && user.name) {
            userGreeting.textContent = `Hi, ${user.name.split(' ')[0]}!`;
        }
        
        this.updateUnreadCount();
    }

    async loadConversations() {
        try {
            const response = await axios.get('/api/conversations.php');
            const conversations = response.data.conversations || [];
            
            this.renderConversations(conversations);
            
            // Auto-select first conversation if available
            if (conversations.length > 0 && !this.currentConversationId) {
                this.selectConversation(conversations[0].id);
            }
        } catch (error) {
            console.error('Failed to load conversations:', error);
            this.showError('Failed to load conversations');
        }
    }

    renderConversations(conversations) {
        const container = document.getElementById('conversations-list');
        if (!container) return;

        if (conversations.length === 0) {
            container.innerHTML = `
                <div class="no-conversations text-center p-4">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No conversations yet</h5>
                    <p class="text-muted">Start swiping to make connections!</p>
                    <a href="/swipe.html" class="btn btn-primary">Start Swiping</a>
                </div>
            `;
            return;
        }

        const conversationsHtml = conversations.map(conv => `
            <div class="conversation-item ${conv.id == this.currentConversationId ? 'active' : ''}" 
                 data-conversation-id="${conv.id}">
                <div class="conversation-avatar">
                    <img src="${conv.avatar || '/pics/default-avatar.jpg'}" 
                         alt="${conv.name}" class="avatar-img">
                    ${conv.online ? '<div class="online-indicator"></div>' : ''}
                </div>
                <div class="conversation-info">
                    <div class="conversation-name">${this.escapeHtml(conv.name)}</div>
                    <div class="conversation-preview">${this.escapeHtml(conv.last_message || 'No messages yet')}</div>
                    <div class="conversation-time">${this.formatTime(conv.last_message_time)}</div>
                </div>
                ${conv.unread_count > 0 ? `<div class="unread-badge">${conv.unread_count}</div>` : ''}
            </div>
        `).join('');

        container.innerHTML = conversationsHtml;
    }

    async selectConversation(conversationId) {
        if (this.currentConversationId === conversationId) return;

        this.stopPolling();
        this.currentConversationId = conversationId;
        this.lastMessageId = 0;

        // Update UI
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('active');
        }

        // Load messages
        await this.loadMessages();
        this.startPolling();
        
        // Mark as read
        this.markConversationAsRead(conversationId);
    }

    async loadMessages() {
        if (!this.currentConversationId) return;

        try {
            const response = await axios.get(`/api/messages.php?conversation_id=${this.currentConversationId}`);
            const messages = response.data.messages || [];
            
            this.renderMessages(messages);
            
            if (messages.length > 0) {
                this.lastMessageId = Math.max(...messages.map(m => m.id));
            }
            
            this.scrollToBottom();
        } catch (error) {
            console.error('Failed to load messages:', error);
            this.showError('Failed to load messages');
        }
    }

    renderMessages(messages) {
        const container = document.getElementById('messages-container');
        if (!container) return;

        if (messages.length === 0) {
            container.innerHTML = `
                <div class="no-messages text-center p-4">
                    <i class="fas fa-comment fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No messages yet. Say hello!</p>
                </div>
            `;
            return;
        }

        const messagesHtml = messages.map(message => {
            const isOwn = message.is_own;
            return `
                <div class="message ${isOwn ? 'sent' : 'received'}" data-message-id="${message.id}">
                    <div class="message-bubble">
                        ${this.escapeHtml(message.content)}
                        <div class="message-time">${this.formatTime(message.created_at)}</div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = messagesHtml;
    }

    async handleSendMessage(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        
        if (!messageInput || !this.currentConversationId) return;
        
        const content = messageInput.value.trim();
        if (!content) return;

        // Disable form
        messageInput.disabled = true;
        sendButton.disabled = true;
        sendButton.innerHTML = '<span class="loading-spinner"></span>';

        try {
            const response = await axios.post('/api/send-message.php', {
                conversation_id: this.currentConversationId,
                content: content
            });

            if (response.data.success) {
                messageInput.value = '';
                this.autoResizeTextarea.call(messageInput);
                
                // Add message to UI immediately
                this.addMessageToUI({
                    id: response.data.message_id,
                    content: content,
                    is_own: true,
                    created_at: new Date().toISOString()
                });
                
                this.scrollToBottom();
                this.lastMessageId = response.data.message_id;
            } else {
                throw new Error(response.data.message || 'Failed to send message');
            }
        } catch (error) {
            console.error('Failed to send message:', error);
            this.showError('Failed to send message. Please try again.');
        } finally {
            // Re-enable form
            messageInput.disabled = false;
            sendButton.disabled = false;
            sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
            messageInput.focus();
        }
    }

    addMessageToUI(message) {
        const container = document.getElementById('messages-container');
        if (!container) return;

        // Remove "no messages" placeholder if present
        const noMessages = container.querySelector('.no-messages');
        if (noMessages) {
            noMessages.remove();
        }

        const isOwn = message.is_own;
        const messageElement = document.createElement('div');
        messageElement.className = `message ${isOwn ? 'sent' : 'received'}`;
        messageElement.dataset.messageId = message.id;
        messageElement.innerHTML = `
            <div class="message-bubble">
                ${this.escapeHtml(message.content)}
                <div class="message-time">${this.formatTime(message.created_at)}</div>
            </div>
        `;

        container.appendChild(messageElement);
    }

    async startPolling() {
        if (this.isPolling || !this.currentConversationId) return;
        
        this.isPolling = true;
        this.pollingInterval = setInterval(() => {
            this.pollForNewMessages();
        }, this.pollDelay);
    }

    stopPolling() {
        this.isPolling = false;
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    async pollForNewMessages() {
        if (!this.currentConversationId || !this.isPolling) return;

        try {
            const response = await axios.get(`/api/poll-messages.php?conversation_id=${this.currentConversationId}&last_message_id=${this.lastMessageId}`);
            
            const newMessages = response.data.messages || [];
            
            if (newMessages.length > 0) {
                newMessages.forEach(message => {
                    this.addMessageToUI(message);
                });
                
                this.scrollToBottom();
                this.lastMessageId = Math.max(...newMessages.map(m => m.id));
                
                // Play notification sound for received messages
                const receivedMessages = newMessages.filter(m => !m.is_own);
                if (receivedMessages.length > 0) {
                    this.playNotificationSound();
                }
            }
        } catch (error) {
            console.error('Polling failed:', error);
            // Don't show error for polling failures to avoid spam
        }
    }

    async markConversationAsRead(conversationId) {
        try {
            await axios.post('/api/mark-read.php', {
                conversation_id: conversationId
            });
            
            this.updateUnreadCount();
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    }

    async updateUnreadCount() {
        try {
            const response = await axios.get('/api/unread-count.php');
            const count = response.data.count || 0;
            
            const badge = document.getElementById('unread-count-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to update unread count:', error);
        }
    }

    scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    autoResizeTextarea() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    }

    playNotificationSound() {
        // Create a subtle notification sound using Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (error) {
            // Ignore audio errors
            console.log('Notification sound not available');
        }
    }

    formatTime(timestamp) {
        if (!timestamp) return '';
        
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    }

    logout() {
        this.stopPolling();
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = '/logout.php';
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.chatManager = new ChatManager();
});

// Export for global use
window.ChatManager = ChatManager;