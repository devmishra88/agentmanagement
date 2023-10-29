// authSlice.js
import { createSlice } from '@reduxjs/toolkit';

const initialState = {
  user: null,
  isAuthenticated: false,
  token: JSON.parse(localStorage.getItem(`${process.env.REACT_APP_STORAGE_KEY}`)) || null,  logoutpopupposition: `bottom`,
  logoutpopupstatus: false,
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setUser: (state, action) => {
      state.user = action.payload;
      state.isAuthenticated = true;
    },
    setToken: (state, action) => {
      state.token = action.payload;
      localStorage.setItem(`${process.env.REACT_APP_STORAGE_KEY}`, JSON.stringify(action.payload));
    },
    logout: (state, action) => {
      localStorage.removeItem(process.env.REACT_APP_STORAGE_KEY);
    
      return {
        ...state,
        user: null,
        isAuthenticated: false,
        token: null,
        ...action.payload,
      };
    },
    confirmLogout: (state, action) => {
      return {
        ...state,
        ...action.payload,
      };
    },
  },
});

export const { setUser, setToken, logout, confirmLogout } = authSlice.actions;
export const selectUser = (state) => state.auth.user;
export const selectIsAuthenticated = (state) => state.auth.isAuthenticated;
export const selectToken = (state) => state.auth.token;

export default authSlice.reducer;
