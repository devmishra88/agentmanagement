import {createSlice, createAsyncThunk} from "@reduxjs/toolkit"

const initialState = {

}

export const CommonSlice = createSlice({
    name:`CommonSlice`,
    initialState,
    reducers: {
        togglePopup:(state, action)=>{
            return{
                ...state,
                ...action.payload
            }
        }
    }
})

export const {togglePopup} = CommonSlice.actions

export default CommonSlice.reducer
