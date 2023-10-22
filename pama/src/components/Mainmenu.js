import React from "react";
import { InboxIcon, MailIcon } from "../constants";
import { useSelector, useDispatch } from "react-redux";
import { toggleMenu } from "../slices/CommonSlice";

import {
  Box,
  Drawer,
  List,
  Divider,
  ListItem,
  ListItemButton,
  ListItemIcon,ListItemText,
} from "@mui/material";

function Mainmenu() {
  const dispatch = useDispatch();
  const { menuposition, menustatus } = useSelector((state) => state.common);

  const list = () => (
    <Box
      sx={{ width: 250 }}
      role="presentation"
      onClick={() =>
        dispatch(
          dispatch(toggleMenu({ menuposition: `left`, menustatus: true }))
        )
      }
    >
      <List>
        {["Inbox", "Starred", "Send email", "Drafts"].map((text, index) => (
          <ListItem key={text} disablePadding>
            <ListItemButton>
              <ListItemIcon>
                {index % 2 === 0 ? <InboxIcon /> : <MailIcon />}
              </ListItemIcon>
              <ListItemText primary={text} />
            </ListItemButton>
          </ListItem>
        ))}
      </List>
      <Divider />
      <List>
        {["All mail", "Trash", "Spam"].map((text, index) => (
          <ListItem key={text} disablePadding>
            <ListItemButton>
              <ListItemIcon>
                {index % 2 === 0 ? <InboxIcon /> : <MailIcon />}
              </ListItemIcon>
              <ListItemText primary={text} />
            </ListItemButton>
          </ListItem>
        ))}
      </List>
    </Box>
  );

  return (
    <>
      <Drawer
        anchor={menuposition}
        open={menustatus}
        onClose={() =>
          dispatch(
            dispatch(toggleMenu({ menuposition: `left`, menustatus: false }))
          )
        }
      >
        {list()}
      </Drawer>
    </>
  );
}

export default Mainmenu;
