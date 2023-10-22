import React, { useEffect } from "react";
import { Outlet, Navigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import {
  selectToken,
  selectIsAuthenticated,
  logout,
} from "../slices/authSlice";

const PrivateRoutes = () => {
  const isAuthenticated = useSelector(selectIsAuthenticated);
  const token = useSelector(selectToken);
  const dispatch = useDispatch();

  // let auth = {'token':false}

  useEffect(() => {
    if (!isAuthenticated) {
      // User is not authenticated, redirect to login page
      //navigate('/login');
    } else if (isAuthenticated && token) {
      // Perform server validation if a token exists in local storage
      // Implement your server validation logic here
      // If validation fails, log out the user
      // Example: If your server returns an error, dispatch(logout()) and redirect to login page
    }
  }, [isAuthenticated, token, dispatch]);

  const isAuthenticated2 = true

  return isAuthenticated2 ? <Outlet /> : <Navigate to="/" />;
};

export default PrivateRoutes;
