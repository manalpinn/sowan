/**
 * Utility to manage transient application state in memory.
 * All operations are kept in RAM, ensuring zero reliance on browser localStorage.
 * This completely avoids mobile browser crashes/restrictions (e.g. Safari Private, WebViews).
 */

const memoryStorage = {};

export const SafeStorage = {
  /**
   * Always returns false since localStorage is disabled/removed.
   */
  isAvailable() {
    return false;
  },

  /**
   * Get an item from the in-memory storage safely.
   */
  getItem(key, defaultValue = null) {
    try {
      return key in memoryStorage ? memoryStorage[key] : defaultValue;
    } catch (e) {
      console.warn(`SafeStorage: Failed to get item "${key}":`, e.message);
      return defaultValue;
    }
  },

  /**
   * Set an item in the in-memory storage safely.
   */
  setItem(key, value) {
    try {
      memoryStorage[key] = String(value);
      return true;
    } catch (e) {
      console.warn(`SafeStorage: Failed to set item "${key}":`, e.message);
      return false;
    }
  },

  /**
   * Remove an item from the in-memory storage safely.
   */
  removeItem(key) {
    try {
      delete memoryStorage[key];
      return true;
    } catch (e) {
      console.warn(`SafeStorage: Failed to remove item "${key}":`, e.message);
      return false;
    }
  },

  /**
   * Parse a JSON string safely.
   */
  parseJson(jsonString, fallbackValue = null) {
    if (!jsonString) return fallbackValue;
    try {
      return JSON.parse(jsonString);
    } catch (e) {
      console.warn('SafeStorage: Failed to parse JSON:', e.message);
      return fallbackValue;
    }
  }
};


