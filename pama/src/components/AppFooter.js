// import applogo from "../assets/images/logo.png";
// import newlogo from "../assets/images/newlogo.png";

import {
  Box,
  Button,
  AppBar,
  Toolbar,
} from "@mui/material";

import {SaveIcon} from "../constants"

function AppFooter({ ...props }) {
  const { children } = props;
  return (
    <AppBar
      position="fixed"
      sx={{ top: "auto", bottom: 0, background: `#F7F7F8`,boxShadow:`0px 2px 4px 3px #00000033` }}
    >
      <Toolbar>
        <Box sx={{ flexGrow: 1 }} />

        <Button
          color="primary"
          sx={{
            display: "flex",
            alignItems: "center",
            gap: "7px",
            width: "120px",
            fontWeight: "700",
            cursor: "pointer",
          }}
          variant="contained"
        >
          <SaveIcon />
          SAVE
        </Button>
      </Toolbar>
    </AppBar>
  );
}

export default AppFooter;
