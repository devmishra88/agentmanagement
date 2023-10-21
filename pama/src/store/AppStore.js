import { configureStore } from "@reduxjs/toolkit";

import CommonReducer from "../slices/CommonSlice";
import authReducer from "../slices/authSlice";

export const store = configureStore({
  reducer: {
    auth: authReducer,
    common: CommonReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: false,
    }),
});
