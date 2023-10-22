import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";

const initialState = {
    menuposition: `left`,
    menustatus: false,
};

export const CommonSlice = createSlice({
  name: `CommonSlice`,
  initialState,
  reducers: {
    togglePopup: (state, action) => {
      return {
        ...state,
        ...action.payload,
      };
    },
    toggleMenu: (state, action) => {
      return {
        ...state,
        ...action.payload,
      };
    },
  },
});

export const { togglePopup, toggleMenu } = CommonSlice.actions;

export default CommonSlice.reducer;
