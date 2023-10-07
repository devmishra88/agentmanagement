import { configureStore } from "@reduxjs/toolkit";

import CommonReducer from "../slices/CommonSlice";

export const store = configureStore({
  reducer: {
    common: CommonReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: false,
    }),
});
