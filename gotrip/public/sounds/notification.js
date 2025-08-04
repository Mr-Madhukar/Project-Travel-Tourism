// This file contains the notification sound data in base64 format
// Uses the Web Audio API to create and play notification sounds

class NotificationSound {
  constructor() {
    this.audioContext = null;
    this.notificationBuffer = null;
    this.initialized = false;
  }

  // Initialize the audio context
  async initialize() {
    try {
      this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
      
      // Convert base64 notification sound to audio buffer
      const base64Sound = "UklGRnQHAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YU8HAACBgIF/gn6Dfn+AgX6BfICAfX9/gH+CgIWChISChIGCgYB/gH2Ae3t4d3Z2dHZ2eHl8fICDhoqMj5KVl5manJycm5mYlpOQjouHg396eHRwbGhlYV5bWFdWVlhZXF9jaGxwdXp/hIiNkZWZnaCipKWlpKKgnpuXk46JhIB7dnJua2dmZGNjZGVmaGptcHN3e36ChYmNkJOWmJqbnJ2cm5qYlpSRjoqGgn56dnJvbGhlY2FgX19gYWNlaGptcXV4fICEh4uOkZSWmJqbm5uamZiVk5COi4eCfnp2c29saWZkYmFgYGBhY2VnaWxvc3Z6fYGEiIuOkZOVl5iZmZmYl5WTkY6LiIR/e3h0cW5raGZkY2JiYmNkZWdpa25xdHd7foGEh4qNj5KUlZeXmJeWlZOQjoqHg396d3RxbmtpZ2VkY2NjZGVmaGpsbXF0d3p+gYSHio2PkZOUlZaWlZSTkY+MiYWBfXl2c3BubGppaGhoaWprbG5wcnV3en2AgoWHio2PkZKTlJSUk5KQjoqHg4B8eXZzcW9tbGtraWpqa2xsbW9xc3Z4e36BhIaJi46PkZKSkpKRkI6MiYaCf3t4dXNxb21sa2tsbG1tbm9wcnR2eXt+gYOGiIqMjo+QkJCQj46MioiEgX56d3VycG9ubW1tbm5vb3Bxc3R2eHp9f4KFh4mLjY6PkJCPj46MioeEgX97eHZ0cnFwb29vcHBxcXJzdHZ3eXt+gIKFh4mLjI2Oj4+Ojo2LiYaEgX98eXd1c3JxcHBwcXFycnN0dXd5e31/goSGiImLjI2Njo2NjIuJh4SCgH58eXd2dHNycXFxcnJzc3R1dnd5e31/gYOFh4iKi4yMjI2MjIqIhoOBf316eHZ1dHNycnJyc3N0dHV2d3l7fH6AgoSGh4mKi4uMjIuLioiGhIKAfXt5eHZ1dHNzc3NzdHR1dXZ3eHp8fX+Bg4WGiImKi4uLi4uKiYeGhIKAfn17enh3dnV1dHR0dXV1dnd4eXp7fX+AgoSFhoeJiYqKioqJiIeGhIOBgH59e3p5eHd2dnZ2dnZ2d3d4eXp7fH5/gYKEhYaHiImJiYmJiIeGhYOCgH9+fHt6eXh4d3d3d3d3eHh4eXp7fH1/gIGDhIWGh4eIiIiIh4aGhIOCgYB+fXx7eno5OXh4eHh4eHl5ent8fX6AgoOEhYaGh4eHh4eHhoWEg4KBgH9+fXx7eno5OXl5eXl5enp7fH1+f4CBgoOEhYWGhoaGhoaFhYSEg4KBgH9+fXx8e3t6enp6enp6e3t8fH1+f4CAgYKDhISFhYWFhYWFhISDgoKBgH9/fn18e3t7e3t6e3t7e3t8fH1+f3+AgYKCg4SEhISEhISEg4ODgoGBgIB/fn19fHx8e3t7e3t7e3x8fH1+fn+AgIGCgoODg4SEhISDg4OCgoGBgIB/f359fX18fHx8fHx8fHx8fX19fn9/gICBgYKCg4ODg4ODg4OCgoKBgYCAgH9/fn59fX19fHx8fHx9fX19fn5/f4CAgYGBgoKCg4ODg4OCgoKCgYGBgICAf39+fn59fX19fX19fX19fn5+f3+AgICBgYGCgoKCgoKCgoKCgoGBgYCAgH9/f359fX19fX19fX1+fn5+f39/gICAgYGBgYKCgoKCgoKCgYGBgYGAgICAf39/fn5+fn5+fn5+fn5+f39/f4CAgICBgYGBgYGBgYGBgYGBgYCAgICAf39/f35+fn5+fn5+fn5+f39/f39/gICAgICBgYGBgYGBgYGBgYGBgICAgIB/f39/f35+fn5+fn5+fn9/f39/f3+AgICAgICAgYGBgYGBgYGBgYGAgICAgICAf39/f39/f39/f39/f39/f3+AgICAgICAgICAgYGBgYGBgYGBgICAgICAgICAgH9/f39/f39/f39/f39/gICAgICAgICAgICAgICAgYGBgYCAgICAgICAgICAgICAf39/f39/f39/f39/f4CAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAf39/f39/f39/f39/gA==";
      
      // Decode base64 to array buffer
      const binaryString = window.atob(base64Sound);
      const len = binaryString.length;
      const bytes = new Uint8Array(len);
      for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
      }
      
      // Create audio buffer from array buffer
      this.notificationBuffer = await this.audioContext.decodeAudioData(bytes.buffer);
      this.initialized = true;
      return true;
    } catch (error) {
      console.error("Error initializing notification sound:", error);
      return false;
    }
  }

  // Play notification sound with optional volume control
  play(volume = 0.5) {
    if (!this.initialized) {
      console.warn("Notification sound not initialized. Call initialize() first.");
      return false;
    }

    try {
      // Create source node and connect to destination
      const source = this.audioContext.createBufferSource();
      source.buffer = this.notificationBuffer;
      
      // Add volume control
      const gainNode = this.audioContext.createGain();
      gainNode.gain.value = volume;
      
      source.connect(gainNode);
      gainNode.connect(this.audioContext.destination);
      
      // Play the sound
      source.start(0);
      return true;
    } catch (error) {
      console.error("Error playing notification sound:", error);
      return false;
    }
  }
}

// Export the notification sound class
window.NotificationSound = NotificationSound; 