import React, { useEffect } from "react";
import { Outlet, Navigate, useNavigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import {
  selectToken,
  selectIsAuthenticated,
  logout,
} from "../slices/authSlice";

const PrivateRoutes = () => {
  const navigate = useNavigate();
  const isAuthenticated = useSelector(selectIsAuthenticated);
  const token = useSelector(selectToken);
  const dispatch = useDispatch();

  useEffect(() => {
    if (!token?.accesstoken) {
      navigate('/');
    } else if (token?.accesstoken && token?.authtoken) {
      /*perform server validation before any action*/
    }
  }, [token, dispatch]);

  return token?.accesstoken ? <Outlet /> : <Navigate to="/" />;
};

export default PrivateRoutes;
