import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";

const initialState = {
  menuposition: `left`,
  menustatus: false,
  toaststatus: false,
  toastmsg: ``,
  loaderstatus:false,
  deletepopupposition:`bottom`,
  deletepopupstatus: false,
  candelete: false,
  deletionrecordid:``,
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
    handleToastMsg: (state, action) => {
      return {
        ...state,
        ...action.payload,
      };
    },
    toggleLoader: (state, action) => {
      return {
        ...state,
        ...action.payload,
      };
    },
    confirmDelete: (state, action) => {
      return {
        ...state,
        ...action.payload,
      };
    },
  },
});

export const { togglePopup, toggleMenu, handleToastMsg, toggleLoader, confirmDelete } = CommonSlice.actions;

export default CommonSlice.reducer;
