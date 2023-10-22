import { AppBar, Toolbar, Typography } from "@mui/material";
import { useDispatch } from "react-redux";
import { toggleMenu } from "../slices/CommonSlice";
import { IconButton } from "@mui/material";
import { MenuIcon } from "../constants";

function AppHeader({ ...props }) {
  const dispatch = useDispatch();

  const { children } = props;
  return (
    <AppBar position="static">
      <Toolbar
        sx={{
          background: `#F7F7F8`,
          color: `#000000`,
        }}
      >
        <IconButton
          onClick={() =>
            dispatch(toggleMenu({ menuposition: `left`, menustatus: true }))
          }
        >
          <MenuIcon
            className="material-icons"
            sx={{
              color: `#007AFF`,
            }}
          />
        </IconButton>
        {/* <img
          src={newlogo}
          alt="App Logo"
          style={{
            width: `100px`,
          }}
        /> */}
        <Typography
          variant="h6"
          sx={{
            ml: 1,
            fontSize: 18,
            fontWeight: `bold`,
          }}
        >
          {children}
        </Typography>
      </Toolbar>
    </AppBar>
  );
}

export default AppHeader;
