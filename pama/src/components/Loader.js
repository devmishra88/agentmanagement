import { Backdrop, CircularProgress } from "@mui/material";
import { useSelector } from "react-redux";

function Loader({ ...props }) {
  const { open } = props;
  const { loaderstatus } = useSelector((state) => state.common);

  return (
    <Backdrop
      sx={{ color: "#fff", zIndex: (theme) => theme.zIndex.drawer + 1 }}
      open={loaderstatus}
    >
      <CircularProgress color="inherit" />
    </Backdrop>
  );
}

export default Loader;
