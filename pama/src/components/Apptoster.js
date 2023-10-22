import { Typography, IconButton, Snackbar } from "@mui/material";
import { useSelector, useDispatch } from "react-redux";
import { handleToastMsg } from "../slices/CommonSlice";
import {CloseIcon} from "../constants"

function Apptoster({ ...props }) {
  const dispatch = useDispatch();
  const { toaststatus, toastmsg } = useSelector((state) => state.common);

  const action = (
    <>
      <IconButton
        size="small"
        aria-label="close"
        color="inherit"
        onClick={() => dispatch(handleToastMsg({ toaststatus: false, toastmsg: `` }))}
      >
        <CloseIcon fontSize="small" />
      </IconButton>
    </>
  );

  return (
    <Snackbar
      autoHideDuration={6000}
      anchorOrigin={{ vertical: "bottom", horizontal: "center" }}
      open={toaststatus}
      onClose={() => dispatch(handleToastMsg({ toaststatus: false, toastmsg: `` }))}
      message={
        <Typography
          sx={{
            fontSize: 14,
          }}
        >
          {toastmsg}
        </Typography>
      }
      key={`bottom` + `center`}
      action={action}
    />
  );
}

export default Apptoster;
